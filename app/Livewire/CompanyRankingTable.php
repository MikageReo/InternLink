<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\PlacementApplication;
use Illuminate\Support\Facades\DB;

class CompanyRankingTable extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'student_count';
    public $sortDirection = 'desc';
    public $perPage = 15;
    public $showDetailModal = false;
    public $selectedCompany = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'student_count'],
        'sortDirection' => ['except' => 'desc'],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function viewCompanyDetails($companyName)
    {
        $this->selectedCompany = $this->getCompanyDetails($companyName);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedCompany = null;
    }

    private function getCompanyRankings()
    {
        $query = PlacementApplication::where('studentAcceptance', 'Accepted')
            ->select(
                'companyName',
                DB::raw('COUNT(*) as student_count'),
                DB::raw('COUNT(DISTINCT companyState) as state_count'),
                DB::raw('MIN(applicationDate) as first_accepted'),
                DB::raw('MAX(applicationDate) as last_accepted'),
                DB::raw('AVG(allowance) as avg_allowance'),
                DB::raw('GROUP_CONCAT(DISTINCT companyState) as states'),
                DB::raw('GROUP_CONCAT(DISTINCT companyCity) as cities')
            )
            ->groupBy('companyName');

        // Apply search filter
        if ($this->search) {
            $query->where('companyName', 'like', '%' . $this->search . '%');
        }

        // Apply sorting
        $allowedSortFields = ['companyName', 'student_count', 'last_accepted', 'avg_allowance'];
        if (in_array($this->sortField, $allowedSortFields)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->orderBy('student_count', 'desc');
        }

        return $query;
    }

    private function getCompanyDetails($companyName)
    {
        $acceptedApplications = PlacementApplication::where('companyName', $companyName)
            ->where('studentAcceptance', 'Accepted')
            ->with(['student.user'])
            ->orderBy('applicationDate', 'desc')
            ->get();

        $totalApplications = PlacementApplication::where('companyName', $companyName)->count();
        $acceptedCount = $acceptedApplications->count();
        $acceptanceRate = $totalApplications > 0 ? round(($acceptedCount / $totalApplications) * 100, 2) : 0;

        $positions = PlacementApplication::where('companyName', $companyName)
            ->where('studentAcceptance', 'Accepted')
            ->select('position', DB::raw('COUNT(*) as count'))
            ->groupBy('position')
            ->orderBy('count', 'desc')
            ->get();

        $workMethods = PlacementApplication::where('companyName', $companyName)
            ->where('studentAcceptance', 'Accepted')
            ->select('methodOfWork', DB::raw('COUNT(*) as count'))
            ->groupBy('methodOfWork')
            ->get();

        $firstApplication = PlacementApplication::where('companyName', $companyName)
            ->where('studentAcceptance', 'Accepted')
            ->orderBy('applicationDate', 'asc')
            ->first();

        $latestApplication = PlacementApplication::where('companyName', $companyName)
            ->where('studentAcceptance', 'Accepted')
            ->orderBy('applicationDate', 'desc')
            ->first();

        return [
            'companyName' => $companyName,
            'totalStudents' => $acceptedCount,
            'totalApplications' => $totalApplications,
            'acceptanceRate' => $acceptanceRate,
            'firstAccepted' => $firstApplication ? $firstApplication->applicationDate->format('Y-m-d') : null,
            'lastAccepted' => $latestApplication ? $latestApplication->applicationDate->format('Y-m-d') : null,
            'averageAllowance' => $acceptedApplications->whereNotNull('allowance')->avg('allowance'),
            'locations' => $acceptedApplications->pluck('companyFullAddress')->unique()->values(),
            'positions' => $positions,
            'workMethods' => $workMethods,
            'students' => $acceptedApplications->map(function ($app) {
                return [
                    'name' => $app->student->user->name ?? 'N/A',
                    'studentID' => $app->student->studentID,
                    'position' => $app->position,
                    'startDate' => $app->startDate?->format('Y-m-d'),
                    'endDate' => $app->endDate?->format('Y-m-d'),
                    'allowance' => $app->allowance,
                ];
            }),
        ];
    }

    public function getRankingBadge($rank)
    {
        if ($rank <= 3) {
            return [
                'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                'icon' => '🏆',
                'text' => 'Top ' . $rank,
            ];
        } elseif ($rank <= 10) {
            return [
                'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                'icon' => '⭐',
                'text' => 'Top 10',
            ];
        }
        return null;
    }

    public function render()
    {
        $rankings = $this->getCompanyRankings()->paginate($this->perPage);

        // Add rank number to each company
        $rankings->getCollection()->transform(function ($company, $index) use ($rankings) {
            $company->rank = ($rankings->currentPage() - 1) * $rankings->perPage() + $index + 1;
            return $company;
        });

        return view('livewire.company-ranking-table', [
            'rankings' => $rankings,
        ]);
    }
}

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
    public $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    private function getCompanyRankings()
    {
        $query = PlacementApplication::where('studentAcceptance', 'Accepted')
            ->select(
                'companyName',
                DB::raw('MAX(companyEmail) as companyEmail'),
                DB::raw('COUNT(*) as student_count'),
                DB::raw('COUNT(DISTINCT companyState) as state_count'),
                DB::raw('MIN(applicationDate) as first_accepted'),
                DB::raw('MAX(applicationDate) as last_accepted'),
                DB::raw('AVG(allowance) as avg_allowance'),
                DB::raw('GROUP_CONCAT(DISTINCT companyState) as states'),
                DB::raw('GROUP_CONCAT(DISTINCT companyCity) as cities'),
                DB::raw('GROUP_CONCAT(DISTINCT position ORDER BY position) as positions')
            )
            ->groupBy('companyName');

        // Apply search filter
        if ($this->search) {
            $query->where('companyName', 'like', '%' . $this->search . '%');
        }

        // Default ordering by student count
        $query->orderBy('student_count', 'desc');

        return $query;
    }

    public function render()
    {
        $rankings = $this->getCompanyRankings()->paginate($this->perPage);

        // Get total company count (before search filter)
        $baseQuery = PlacementApplication::where('studentAcceptance', 'Accepted');
        if ($this->search) {
            $baseQuery->where('companyName', 'like', '%' . $this->search . '%');
        }
        $totalCompanies = $baseQuery->distinct('companyName')->count('companyName');

        return view('livewire.companyRankingTable', [
            'rankings' => $rankings,
            'totalCompanies' => $totalCompanies,
        ]);
    }
}

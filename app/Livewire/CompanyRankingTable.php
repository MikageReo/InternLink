<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Company;
use App\Models\PlacementApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CompanyRankingTable extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 15;
    public $sortBy = 'student_count';
    public $sortDirection = 'desc';
    public $statusFilter = '';
    public $editingCompanyId = null;
    public $editCompanyName = '';
    public $editCompanyEmail = '';
    public $editCompanyNumber = '';
    public $editCompanyAddressLine = '';
    public $editCompanyCity = '';
    public $editCompanyPostcode = '';
    public $editCompanyState = '';
    public $editCompanyCountry = '';
    public $editStatus = 'Active';

    protected $queryString = [
        'search' => ['except' => ''],
        'page' => ['except' => 1],
        'sortBy' => ['except' => 'student_count'],
        'sortDirection' => ['except' => 'desc'],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function isCoordinator()
    {
        $user = Auth::user();
        return $user && $user->lecturer && $user->lecturer->isCoordinator;
    }

    public function editCompany($companyId)
    {
        if (!$this->isCoordinator()) {
            return;
        }

        $company = Company::find($companyId);
        if ($company) {
            $this->editingCompanyId = $companyId;
            $this->editCompanyName = $company->companyName;
            $this->editCompanyEmail = $company->companyEmail ?? '';
            $this->editCompanyNumber = $company->companyNumber ?? '';
            $this->editCompanyAddressLine = $company->companyAddressLine ?? '';
            $this->editCompanyCity = $company->companyCity ?? '';
            $this->editCompanyPostcode = $company->companyPostcode ?? '';
            $this->editCompanyState = $company->companyState ?? '';
            $this->editCompanyCountry = $company->companyCountry ?? '';
            $this->editStatus = $company->status;
        }
    }

    public function cancelEdit()
    {
        $this->resetEditFields();
    }

    public function updateCompany()
    {
        if (!$this->isCoordinator()) {
            return;
        }

        $this->validate([
            'editCompanyName' => 'required|string|max:255',
            'editCompanyEmail' => 'nullable|email|max:255',
            'editCompanyNumber' => 'nullable|string|max:255',
            'editStatus' => 'required|in:Active,Downsize,Blacklisted,Closed Down,Illegal',
        ]);

        $company = Company::find($this->editingCompanyId);
        if ($company) {
            $company->update([
                'companyName' => $this->editCompanyName,
                'companyEmail' => $this->editCompanyEmail,
                'companyNumber' => $this->editCompanyNumber,
                'companyAddressLine' => $this->editCompanyAddressLine,
                'companyCity' => $this->editCompanyCity,
                'companyPostcode' => $this->editCompanyPostcode,
                'companyState' => $this->editCompanyState,
                'companyCountry' => $this->editCompanyCountry,
                'status' => $this->editStatus,
            ]);

            $this->resetEditFields();
            session()->flash('message', 'Company updated successfully.');
        }
    }

    private function resetEditFields()
    {
        $this->editingCompanyId = null;
        $this->editCompanyName = '';
        $this->editCompanyEmail = '';
        $this->editCompanyNumber = '';
        $this->editCompanyAddressLine = '';
        $this->editCompanyCity = '';
        $this->editCompanyPostcode = '';
        $this->editCompanyState = '';
        $this->editCompanyCountry = '';
        $this->editStatus = 'Active';
    }

    private function getCompanyRankings()
    {
        $query = Company::leftJoin('placement_applications', function($join) {
                $join->on('companies.companyName', '=', 'placement_applications.companyName')
                     ->where('placement_applications.studentAcceptance', 'Accepted');
            })
            ->select(
                'companies.*',
                DB::raw('COUNT(placement_applications.applicationID) as student_count'),
                DB::raw('COUNT(DISTINCT placement_applications.companyState) as state_count'),
                DB::raw('MIN(placement_applications.applicationDate) as first_accepted'),
                DB::raw('MAX(placement_applications.applicationDate) as last_accepted'),
                DB::raw('AVG(placement_applications.allowance) as avg_allowance'),
                DB::raw('GROUP_CONCAT(DISTINCT placement_applications.companyState) as states'),
                DB::raw('GROUP_CONCAT(DISTINCT placement_applications.companyCity) as cities'),
                DB::raw('GROUP_CONCAT(DISTINCT placement_applications.position ORDER BY placement_applications.position) as positions')
            )
            ->groupBy('companies.companyID', 'companies.companyName', 'companies.companyEmail', 'companies.companyNumber', 
                     'companies.companyAddressLine', 'companies.companyCity', 'companies.companyPostcode', 
                     'companies.companyState', 'companies.companyCountry', 'companies.companyLatitude', 
                     'companies.companyLongitude', 'companies.industrySupervisorName', 'companies.industrySupervisorContact', 
                     'companies.industrySupervisorEmail', 'companies.status', 'companies.created_at', 'companies.updated_at');

        // Apply search filter
        if ($this->search) {
            $query->where('companies.companyName', 'like', '%' . $this->search . '%');
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('companies.status', $this->statusFilter);
        }

        // Apply sorting
        if ($this->sortBy === 'student_count') {
            $query->orderBy('student_count', $this->sortDirection);
        } elseif ($this->sortBy === 'avg_allowance') {
            $query->orderBy('avg_allowance', $this->sortDirection);
        } elseif ($this->sortBy === 'companyName') {
            $query->orderBy('companies.companyName', $this->sortDirection);
        } elseif ($this->sortBy === 'status') {
            $query->orderBy('companies.status', $this->sortDirection);
        } else {
            $query->orderBy('student_count', 'desc');
        }

        return $query;
    }

    public function render()
    {
        $rankings = $this->getCompanyRankings()->paginate($this->perPage);

        // Get total company count
        $baseQuery = Company::query();
        if ($this->search) {
            $baseQuery->where('companyName', 'like', '%' . $this->search . '%');
        }
        $totalCompanies = $baseQuery->count();

        // Get top companies for chart (sorted by current sort)
        $chartQuery = $this->getCompanyRankings();
        
        // When sorting by allowance, include companies even if they have 0 students
        // Otherwise, only show companies with students
        if ($this->sortBy === 'avg_allowance') {
            $topCompanies = $chartQuery->limit(10)->get();
        } else {
            $topCompanies = $chartQuery
                ->havingRaw('student_count > 0')
                ->limit(10)
                ->get();
        }

        // Prepare chart data
        $chartData = $topCompanies->map(function($company) {
            return [
                'name' => $company->companyName,
                'students' => (int) ($company->student_count ?? 0),
                'allowance' => (float) ($company->avg_allowance ?? 0),
                'status' => $company->status ?? 'Active'
            ];
        })->values();

        return view('livewire.companyRankingTable', [
            'rankings' => $rankings,
            'totalCompanies' => $totalCompanies,
            'topCompanies' => $topCompanies,
            'chartData' => $chartData,
        ]);
    }
}

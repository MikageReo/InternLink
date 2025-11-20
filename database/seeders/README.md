# Database Seeders Guide

## Available Seeders

### 1. PlacementApplicationSeeder
Creates comprehensive test data for internship placement applications.

**What it creates:**
- 20-40 placement applications from existing students
- **UPDATED: More accepted applications for supervisor assignment testing**
- Variety of application statuses:
  - **Student accepted (47% - majority for testing supervisor assignment)**
  - Committee pending (12%)
  - Coordinator pending (12%)
  - Fully approved, awaiting student acceptance (12%)
  - Student declined (6%)
  - Committee/Coordinator rejected (11%)
- Realistic Malaysian companies (Maybank, Grab, Shopee, Intel, etc.)
- Various positions (Software Developer, Data Analyst, Network Engineer, etc.)
- Complete job scopes and company details
- 1-3 PDF files per application (offer letters, acceptance forms, etc.)
- Proper committee and coordinator assignments

**For Supervisor Assignment Testing:**
- Each run creates ~8 students with accepted placements
- Run multiple times to get more students ready for supervisor assignment
- Current test data: **17 students** ready for supervisor assignment

**Prerequisites:**
- Students must exist in the database
- Students should have approved course verifications
- At least one lecturer with committee and/or coordinator role

**Usage:**

```bash
# Run only this seeder
php artisan db:seed --class=PlacementApplicationSeeder

# Or run all seeders (includes this one)
php artisan db:seed
```

### 2. CourseVerificationSeeder
Creates test data for course verification applications.

**Usage:**

```bash
php artisan db:seed --class=CourseVerificationSeeder
```

## Running Seeders in Sequence

For a complete test environment, run seeders in this order:

```bash
# 1. First, ensure you have students and lecturers in your system
#    (These should already exist from your main seeder or manual creation)

# 2. Create course verifications (if not already done)
php artisan db:seed --class=CourseVerificationSeeder

# 3. Create placement applications
php artisan db:seed --class=PlacementApplicationSeeder
```

## Fresh Database with All Test Data

If you want to start fresh:

```bash
# Reset database and run all seeders
php artisan migrate:fresh --seed

# Or reset and run specific seeder
php artisan migrate:fresh
php artisan db:seed --class=PlacementApplicationSeeder
```

## What Gets Created

### PlacementApplicationSeeder Output Example:

```
Creating placement application test data...
Created application #1 for Ahmad bin Abdullah at Maybank Berhad - Status: Pending / Pending
Created application #2 for Siti Nurhaliza at Grab Malaysia - Status: Approved / Pending
Created application #3 for Lee Wei Ming at Shopee Malaysia - Status: Approved / Approved / Accepted
...

✅ Placement application test data created successfully!
📊 Summary:
   - Total applications: 35
   - Committee pending: 12
   - Committee approved: 20
   - Committee rejected: 3
   - Coordinator pending: 8
   - Coordinator approved: 15
   - Coordinator rejected: 2
   - Student accepted: 10
   - Student declined: 2
```

## Testing Bulk Actions

After running the seeder, you can test:

### As Committee Member:
1. Login as a lecturer with committee role
2. Go to Placement Applications page
3. Filter by "Committee Pending"
4. Select multiple applications using checkboxes
5. Click "Committee Approve" or "Committee Reject"
6. Download all files using "Download All Files" button

### As Coordinator:
1. Login as a lecturer with coordinator role
2. Go to Placement Applications page
3. Filter by "Coordinator Pending"
4. Select multiple applications
5. Click "Coordinator Approve" or "Coordinator Reject"
6. Download all files using bulk download

## Sample Data Included

### Companies:
- Maybank Berhad (Banking)
- CIMB Bank Berhad (Banking)
- Petronas Digital Sdn Bhd (Oil & Gas / Technology)
- Grab Malaysia (Technology)
- Shopee Malaysia (E-commerce)
- Axiata Group Berhad (Telecommunications)
- Fusionex International (Data Analytics)
- Intel Malaysia (Technology / Manufacturing)
- Dell Technologies Malaysia (Technology)
- Accenture Malaysia (Consulting)
- DHL Express Malaysia (Logistics)
- PwC Malaysia (Professional Services)
- Deloitte Malaysia (Professional Services)
- KPMG Malaysia (Professional Services)
- IBM Malaysia (Technology)

### Positions:
- Software Developer Intern
- Data Analyst Intern
- Network Engineer Intern
- UI/UX Designer Intern
- Cybersecurity Intern
- Digital Marketing Intern
- IT Support Intern
- Mobile App Developer Intern
- Database Administrator Intern
- DevOps Intern

### Work Methods:
- WFO (Work From Office)
- WFH (Work From Home)
- WFO & WFH (Hybrid)
- WOS (Work On Site)
- WOC (Work On Campus)

### Allowances:
Range from RM600 to RM1500 per month (some with no allowance)

## Customization

To modify the seeder:

1. Edit `database/seeders/PlacementApplicationSeeder.php`
2. Modify the `$companies`, `$positions`, or `$addresses` arrays
3. Adjust `$statusCombinations` to change the distribution of statuses
4. Change the number of applications per student in the loop

## Troubleshooting

### "No students found!"
**Solution:** Ensure students exist in your database. Run user/student seeders first.

### "No lecturers found!"
**Solution:** Ensure lecturers exist. At least one lecturer should have `isCommittee` or `isCoordinator` set to true.

### "No students with approved course verification found!"
**Solution:** The seeder will automatically create course verifications if none exist. If you want to run the CourseVerificationSeeder separately first:

```bash
php artisan db:seed --class=CourseVerificationSeeder
```

### Duplicate applications
**Solution:** The seeder can be run multiple times. It will create new applications each time. To start fresh:

```bash
# Delete all placement applications
php artisan tinker
>>> App\Models\PlacementApplication::truncate();
>>> App\Models\File::where('fileable_type', 'App\Models\PlacementApplication')->delete();
>>> exit

# Then run seeder again
php artisan db:seed --class=PlacementApplicationSeeder
```

## Files Created

All seeded files are stored in:
- `storage/app/public/placement-application-files/`

Each application gets 1-3 PDF files with names like:
- `placement_1_1732123456_0.pdf`
- `placement_1_1732123456_1.pdf`

File types included:
- Offer letters
- Acceptance forms
- Company profiles
- Job descriptions

## Notes

- Email notifications are NOT sent during seeding (by design)
- All dates are generated relative to current date (future start dates)
- Internship duration ranges from 3-6 months
- Companies are assigned realistic Malaysian addresses with coordinates
- Files are dummy PDFs with basic content (suitable for testing)


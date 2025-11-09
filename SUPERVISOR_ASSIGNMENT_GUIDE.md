# Supervisor Assignment Feature Guide

## Overview
The Supervisor Assignment feature allows coordinators to assign supervisors to students who have accepted placement applications. The system uses Google Maps API to calculate distances and recommend the nearest available supervisors.

## Prerequisites

### 1. Google Maps API Key
✅ **Already configured!** Your Google Maps API key is working correctly.

### 2. User Roles Required
- **Coordinator**: Only coordinators can access and use the supervisor assignment feature
- **Supervisor**: Lecturers must be marked as supervisors to be assignable

### 3. Data Requirements
- Students must have accepted placement applications
- Students, lecturers, and placement applications should have address information geocoded

## Features

### 1. **View All Eligible Students**
- Access: `http://your-domain/lecturer/supervisor-assignments`
- Shows statistics: Total Eligible, Assigned, Unassigned
- Search by student ID, name, email, or company
- Filter by assignment status (all, assigned, unassigned)

### 2. **Manual Assignment**
- Click "Assign" button next to an unassigned student
- View student information and placement details
- See recommended supervisors sorted by distance (nearest first)
- Each supervisor shows:
  - Name and ID
  - Department
  - Distance from student's placement location (in km)
  - Quota status (current/total assignments)
  - Available slots

### 3. **Auto Assignment**
- Click "Auto Assign" button for quick assignment
- System automatically assigns the nearest available supervisor
- Considers department matching and quota availability

### 4. **Quota Override**
- Enable "Include supervisors with full quota" checkbox
- Allows assigning supervisors who have reached their quota limit
- **Required**: Provide a reason for quota override
- Useful for special cases or exceptional circumstances

### 5. **Assignment Notes**
- Add optional notes when assigning supervisors
- Useful for documenting special requirements or considerations

### 6. **Distance Calculation**
The system calculates distances using:
1. **Primary**: Student's placement company location (most relevant for supervision)
2. **Fallback**: Student's home address (if company location not available)

### 7. **Department Matching**
- System enforces department/program matching
- Prevents cross-department supervisor assignments
- Ensures supervisors are familiar with the student's field

## How to Use

### For Coordinators:

#### **Step 1: Access the Feature**
```
Login as Coordinator → Dashboard → Supervisor Assignments
```
Or navigate to: `/lecturer/supervisor-assignments`

#### **Step 2: Manual Assignment**
1. Find the student in the list (use search if needed)
2. Click "Assign" button
3. Review student and placement information
4. Browse recommended supervisors (sorted by distance)
5. Select a supervisor from the list
6. Optionally add assignment notes
7. If needed, enable quota override and provide reason
8. Click "Assign Supervisor"

#### **Step 3: Auto Assignment (Quick)**
1. Find the student in the list
2. Click "Auto Assign" button
3. System automatically assigns nearest available supervisor
4. Success message confirms assignment

#### **Step 4: View Assignment Details**
1. Click "View Details" for assigned students
2. See complete assignment information including:
   - Student and supervisor details
   - Distance between them
   - Assignment date and coordinator who assigned
   - Any notes or override information

## Commands

### Test Google Maps API
```bash
php artisan test:geocoding "Your Address Here"
```

### Geocode Existing Data
```bash
# Geocode all records
php artisan geocode:existing-data

# Geocode specific type
php artisan geocode:existing-data --type=students
php artisan geocode:existing-data --type=lecturers
php artisan geocode:existing-data --type=placements
```

## Setting Up Supervisors

### Making a Lecturer a Supervisor:
1. Go to User Directory
2. Find the lecturer
3. Edit their profile
4. Check "Is Supervisor" checkbox
5. Set "Supervisor Quota" (recommended: 5-10 students)
6. Ensure lecturer status is "Active"
7. Ensure lecturer has complete address information

### Supervisor Requirements:
- `is_supervisor` = true
- `supervisor_quota` > 0
- `status` = 'active'
- Same department as student
- Complete address for distance calculation (optional but recommended)

## Validation Rules

The system validates:
1. ✅ Student has accepted placement application
2. ✅ Student doesn't already have an active supervisor
3. ✅ Selected lecturer is marked as supervisor
4. ✅ Supervisor status is active
5. ✅ Supervisor has available quota (unless override enabled)
6. ✅ Supervisor and student are from same department
7. ✅ Override reason provided if quota override enabled

## Email Notifications

When a supervisor is assigned:
- ✅ Student receives email notification
- Email includes supervisor details and contact information
- Email template: `resources/views/emails/supervisor-assignment.blade.php`

## Troubleshooting

### No supervisors showing up?
1. Ensure lecturers are marked as supervisors (`is_supervisor` = true)
2. Check supervisor quota (`supervisor_quota` > `current_assignments`)
3. Verify supervisor status is "Active"
4. Ensure department matches student's program
5. Try enabling "Quota Override" to see all supervisors

### Distance not calculated?
1. Run geocoding command: `php artisan geocode:existing-data`
2. Ensure students have placement applications with company addresses
3. Ensure supervisors have complete address information
4. Check Google Maps API key is configured

### Access denied?
- Only coordinators can access this feature
- Check user's lecturer profile has `isCoordinator` = true

## Database Structure

### Tables:
- `supervisor_assignments` - Main assignment records
- `students` - Student information with geocoding
- `lecturers` - Lecturer/supervisor information with geocoding
- `placement_applications` - Company locations with geocoding

### Key Fields:
- `has_geocoding` - Boolean flag indicating geocoded data
- `latitude`, `longitude` - Geocoded coordinates
- `distance_km` - Calculated distance (stored in assignment)
- `quota_override` - Flag for quota overrides
- `override_reason` - Reason for quota override

## Best Practices

1. **Geocode Data First**: Run `php artisan geocode:existing-data` before assigning supervisors
2. **Set Realistic Quotas**: Typically 5-10 students per supervisor
3. **Document Overrides**: Always provide clear reasons for quota overrides
4. **Use Auto-Assign**: For efficiency when department match and proximity are primary concerns
5. **Review Assignments**: Periodically check assignment distribution across supervisors
6. **Keep Addresses Updated**: Ensure accurate address data for better distance calculations

## Current Status

✅ Google Maps API configured and tested
✅ Geocoding service operational
✅ 2 students geocoded successfully
✅ Supervisor assignment feature ready to use
✅ Distance calculation working
✅ Email notifications configured
✅ Middleware and access control set up

## Next Steps

1. **Set up supervisors**:
   - Mark lecturers as supervisors in User Directory
   - Set their quota limits
   - Ensure they have complete addresses

2. **Geocode supervisor addresses**:
   ```bash
   php artisan geocode:existing-data --type=lecturers
   ```

3. **Verify students have accepted placements**:
   - Students need accepted placement applications
   - Ensure placement applications have company addresses

4. **Start assigning**:
   - Navigate to `/lecturer/supervisor-assignments`
   - Begin manual or auto assignments

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Review Google Maps API usage in Google Cloud Console
- Verify database migrations are up to date
- Ensure all services are properly configured in `.env`


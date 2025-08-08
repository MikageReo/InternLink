# Role-Based Authentication System

This Laravel application now supports role-based authentication with two user roles: **Student** and **Lecturer**.

## Features

### User Roles
- **Student**: Can access student-specific dashboard with features like:
  - Course enrollments
  - Assignment tracking
  - Grade viewing
  - Class schedule
  - Library access
  - Student support

- **Lecturer**: Can access lecturer-specific dashboard with features like:
  - Course management
  - Student management
  - Assignment creation and grading
  - Grade management
  - Schedule management
  - Reports and analytics

### Authentication
- **Automatic Role Detection**: Users only need to enter email and password during login - the system automatically detects their role from the database
- Role selection only during registration (when creating a new account)
- Role-based route protection using middleware
- Automatic redirection to role-specific dashboards
- Role information displayed in navigation

## Setup Instructions

1. **Run Migrations**
   ```bash
   php artisan migrate:fresh --seed
   ```

2. **Test Users Created**
   - **Student**: `student@example.com` / `password`
   - **Lecturer**: `lecturer@example.com` / `password`

## File Structure

### Models
- `app/Models/User.php` - Updated with role functionality and helper methods

### Middleware
- `app/Http/Middleware/CheckRole.php` - Role-based route protection

### Livewire Components
- `app/Livewire/Forms/LoginForm.php` - Login form validation and authentication
- `resources/views/livewire/pages/auth/login.blade.php` - Login component (no role selection needed)
- `resources/views/livewire/pages/auth/register.blade.php` - Registration component with role selection

### Views
- `resources/views/student/dashboard.blade.php` - Student dashboard
- `resources/views/lecturer/dashboard.blade.php` - Lecturer dashboard
- `resources/views/livewire/layout/navigation.blade.php` - Updated navigation

### Routes
- `routes/web.php` - Role-based routing configuration

## Usage

### First Page
- The application's first page is the login page
- Users are automatically redirected to login when visiting the root URL

### Registration
1. Visit `/register`
2. Fill in name, email, password
3. Select role (Student or Lecturer)
4. Complete registration

### Login
1. Visit `/login` (or just visit the root URL `/`)
2. Enter email and password
3. The system automatically detects your role and redirects you to the appropriate dashboard

### Navigation
- Users are automatically redirected to their role-specific dashboard
- Navigation shows role-specific links
- User role is displayed in the dropdown menu

## Security

- Role-based middleware protects routes
- Users can only access their assigned role's dashboard
- Role validation during authentication
- Unauthorized access attempts return 403 error

## Customization

### Adding New Roles
1. Update the migration to include new role values
2. Add role constants to User model
3. Create new dashboard views
4. Add new routes with role middleware
5. Update navigation logic

### Modifying Dashboards
- Edit `resources/views/student/dashboard.blade.php` for student features
- Edit `resources/views/lecturer/dashboard.blade.php` for lecturer features

### Adding Role-Specific Features
- Create new controllers for role-specific functionality
- Add routes with appropriate role middleware
- Update navigation to include new links 

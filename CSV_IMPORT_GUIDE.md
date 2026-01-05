# 📋 CSV Bulk Registration Guide

## Overview
This guide explains how to use CSV files to register multiple students or lecturers at once in the Internlink system.

## 📁 Template Files

Two CSV templates are provided in the `csv_templates` folder:
1. **students_bulk_registration_template.csv** - For registering students
2. **lecturers_bulk_registration_template.csv** - For registering lecturers

## 🚀 Quick Start

### Step 1: Download the Template
Choose the appropriate template:
- For students: `csv_templates/students_bulk_registration_template.csv`
- For lecturers: `csv_templates/lecturers_bulk_registration_template.csv`

### Step 2: Edit the Template
Open the CSV file in Excel, Google Sheets, or any spreadsheet software and:
1. Delete the example rows (keep the header row)
2. Add your actual data
3. Save as CSV format

### Step 3: Upload
1. Login as coordinator/admin
2. Go to **User Directory**
3. Click **"Bulk Registration"** button
4. Choose your CSV file
5. Select **Semester** and **Session** (e.g., 24/25)
6. **For Students Only**: Enter **Course Code** (optional, max 8 characters), **Internship Start Date**, and **Internship End Date**
7. Click **Upload & Register**

### Step 4: Done! ✅
- System will create user accounts automatically
- Generate unique passwords for each user
- Send email notifications with login credentials
- Automatically geocode addresses (if Google Maps API is configured)

## 📊 Student CSV Format

### Required Fields
| Field | Description | Example |
|-------|-------------|---------|
| **studentID** | Unique student ID (required) | CD220001 |
| **name** | Full name (required) | Ahmad Bin Ali |
| **email** | Unique email address (required) | ahmad.ali@example.com |

### Optional Fields
| Field | Description | Example |
|-------|-------------|---------|
| phone | Contact number | 0123456789 |
| address | Street address | No 123 Jalan Utama |
| city | City name | Kuantan |
| postcode | Postal code | 26000 |
| state | State/Province | Pahang |
| country | Country | Malaysia |
| nationality | Nationality | Malaysian |
| program | Degree program code | BCS, BCN, BCM, BCY, DRC |
| academicAdvisorID | Academic advisor lecturer ID | LEC001 |
| latitude | GPS latitude (leave empty for auto-geocode) | 3.8077 |
| longitude | GPS longitude (leave empty for auto-geocode) | 103.3260 |

### Important Notes
- **Session, Course Code, and Internship Dates** are now entered in the upload form, NOT in the CSV file
- These values will be applied to ALL students in the CSV file
- The CSV file should only contain student personal and academic information

### Student CSV Example
```csv
studentID,name,email,phone,address,city,postcode,state,country,nationality,program,academicAdvisorID
CD220001,Ahmad Bin Ali,ahmad.ali@example.com,0123456789,No 123 Jalan Utama,Kuantan,26000,Pahang,Malaysia,Malaysian,BCS,LEC001
CD220002,Siti Fatimah,siti.fatimah@example.com,0198765432,No 456 Jalan Permai,Kuala Lumpur,50000,Wilayah Persekutuan,Malaysia,Malaysian,BCN,LEC002
```

**Note**: Course Code, Session, and Internship Dates are entered in the upload form, not in the CSV.

### Minimal Student CSV (Required fields only)
```csv
studentID,name,email
CD220001,Ahmad Bin Ali,ahmad.ali@example.com
CD220002,Siti Fatimah,siti.fatimah@example.com
```

## 👨‍🏫 Lecturer CSV Format

### Required Fields
| Field | Description | Example |
|-------|-------------|---------|
| **lecturerID** | Unique lecturer ID (required) | LEC001 |
| **name** | Full name (required) | Dr. Abdullah Rahman |
| **email** | Unique email address (required) | abdullah.rahman@example.com |

### Optional Fields - Basic Info
| Field | Description | Example |
|-------|-------------|---------|
| staffGrade | Staff grade/level | DG54 |
| role | Role in system | Lecturer |
| position | Job position | Senior Lecturer |
| address | Street address | Faculty of Computing |
| city | City name | Kuantan |
| postcode | Postal code | 26300 |
| state | State/Province | Pahang |
| country | Country | Malaysia |
| researchGroup | Research area/group | Software Engineering |
| department | Department/Faculty | Faculty of Computing |
| latitude | GPS latitude (leave empty for auto-geocode) | 3.8077 |
| longitude | GPS longitude (leave empty for auto-geocode) | 103.3260 |

### Optional Fields - Quotas & Roles
| Field | Description | Values | Default |
|-------|-------------|--------|---------|
| studentQuota | Max students as advisor | Number (e.g., 10) | 0 |
| isAcademicAdvisor | Can be academic advisor | true/false | false |
| isSupervisorFaculty | Faculty supervisor role | true/false | false |
| isCommittee | Committee member | true/false | false |
| isCoordinator | Program coordinator | true/false | false |
| isAdmin | System administrator | true/false | false |
| **is_supervisor** | Can supervise placements | true/false | false |
| **supervisor_quota** | Max placement students | Number (e.g., 5) | 0 |

### Lecturer CSV Example
```csv
lecturerID,name,email,staffGrade,role,position,department,studentQuota,isAcademicAdvisor,is_supervisor,supervisor_quota
LEC001,Dr. Abdullah Rahman,abdullah.rahman@example.com,DG54,Lecturer,Senior Lecturer,Faculty of Computing,10,true,false,0
LEC002,Prof. Dr. Noor Azlina,noor.azlina@example.com,VK7,Professor,Professor,Faculty of Computing,8,true,true,5
LEC003,Dr. Muhammad Faiz,faiz.muhammad@example.com,DG52,Lecturer,Lecturer,Faculty of Computing,12,true,true,8
```

**Note**: Session is entered in the upload form, not in the CSV.

### Minimal Lecturer CSV (Required fields only)
```csv
lecturerID,name,email
LEC001,Dr. Abdullah Rahman,abdullah.rahman@example.com
LEC002,Prof. Dr. Noor Azlina,noor.azlina@example.com
```

## 🔍 Important Notes

### Upload Form Fields

**For All Users (Students & Lecturers):**
- **Semester**: Select Semester 1 or Semester 2
- **Session**: Enter in YY/YY format (e.g., 24/25 for 2024/2025)

**For Students Only:**
- **Course Code**: Optional, maximum 8 characters (e.g., CS123456)
- **Internship Start Date**: Required, format YYYY-MM-DD (e.g., 2024-06-01)
- **Internship End Date**: Required, format YYYY-MM-DD, must be after start date (e.g., 2024-12-31)

**Important**: These values are entered in the upload form and will be applied to ALL records in your CSV file. Do NOT include these fields in your CSV file.

### Boolean Fields (true/false)
For yes/no fields, use:
- `true` = Yes/Enabled
- `false` = No/Disabled
- Leave empty = Defaults to false

### Supervisor vs Academic Advisor
- **Academic Advisor** (`isAcademicAdvisor`): General academic mentoring
- **Supervisor** (`is_supervisor`): Supervises internship placements
- A lecturer can be both!

### Quotas
- **studentQuota**: Maximum students for academic advising
- **supervisor_quota**: Maximum students for placement supervision
- Set to `0` if not applicable

### Geocoding (Location)
- **With Address**: Leave latitude/longitude empty, system will auto-geocode
- **Without Address**: Provide latitude/longitude manually
- Geocoding requires Google Maps API key to be configured

### Email Notifications
- Each new user receives an email with:
  - Their username (email)
  - Auto-generated password
  - Login instructions
- Password is randomly generated (format: uniqid)

## 📝 Common Programs (for reference)

### Program Codes
- **BCS**: Bachelor of Computer Science (Software Engineering) with Honours
- **BCN**: Bachelor of Computer Science (Computer Systems & Networking) with Honours
- **BCM**: Bachelor of Computer Science (Multimedia Software) with Honours
- **BCY**: Bachelor of Computer Science (Cyber Security) with Honours
- **DRC**: Diploma in Computer Science

## ⚠️ Common Mistakes to Avoid

### 1. Missing Header Row
❌ **Wrong**: Delete the header row
```csv
CD220001,Ahmad Bin Ali,ahmad.ali@example.com
```

✅ **Correct**: Keep the header row
```csv
studentID,name,email
CD220001,Ahmad Bin Ali,ahmad.ali@example.com
```

### 2. Wrong File Type
❌ **Wrong**: Saving as Excel (.xlsx)
✅ **Correct**: Save as CSV (.csv)

### 3. Duplicate IDs/Emails
❌ **Wrong**: Using same ID or email twice
✅ **Correct**: Each ID and email must be unique

### 4. Boolean Values
❌ **Wrong**: Using `yes`, `no`, `1`, `0`, `TRUE`, `FALSE`
✅ **Correct**: Use lowercase `true` or `false`

### 5. Empty Required Fields
❌ **Wrong**: Leaving studentID, name, or email empty
✅ **Correct**: Fill all required fields

### 6. Extra Spaces
❌ **Wrong**: `CD220001 ` (space after)
✅ **Correct**: `CD220001` (no extra spaces)

### 7. Wrong Session Format
❌ **Wrong**: `2024/2025`, `24-25`, `2024`
✅ **Correct**: `24/25` (YY/YY format)

### 8. Course Code Too Long
❌ **Wrong**: `CS123456789` (9 characters)
✅ **Correct**: `CS123456` (max 8 characters)

### 9. Invalid Date Format
❌ **Wrong**: `01/06/2024`, `2024-6-1`
✅ **Correct**: `2024-06-01` (YYYY-MM-DD format)

## 🔧 System Behavior

### Auto-Generated
- **User Account**: Created automatically
- **Password**: Unique random password (sent via email)
- **User Role**: Set based on file type (student/lecturer)
- **Status**: Set to 'active' by default
- **Semester**: Set from upload form
- **Session**: Converted from Year in upload form, or from CSV if provided

### Auto-Detection
- **File Type**: Detected by header (studentID = students, lecturerID = lecturers)
- **Geocoding**: Attempts to geocode addresses automatically if coordinates not provided
- **Session Conversion**: Automatically converts year (YYYY) to session (YY/YY) format

### Validation
System checks:
- ✅ Unique student/lecturer ID
- ✅ Unique email address
- ✅ Valid email format
- ✅ Required fields not empty
- ✅ Course code max 8 characters (if provided)
- ✅ Session format valid (YY/YY)
- ✅ Internship end date after start date (if provided)

## 📊 Upload Results

After upload, you'll see:
- ✅ **Success Count**: Number of users created
- ❌ **Error Count**: Number of failed rows
- 📝 **Error Details**: Specific errors for each failed row

### Success
```
Registration completed! 25 users created successfully.
```

### Partial Success
```
Registration completed! 22 users created successfully. 3 errors occurred.

Errors:
- Row 5: Email already exists
- Row 12: Student ID already exists
- Row 18: Invalid email format
```

## 💡 Best Practices

### 1. Test with Small Batches First
- Upload 2-3 users first to test
- Verify everything works
- Then upload full batch

### 2. Keep Backup
- Save original CSV file
- Keep copy of uploaded data
- Track which users were added

### 3. Verify Emails
- Use real email addresses
- Check for typos
- Test email delivery

### 4. Consistent Formatting
- Use same date format throughout (YYYY-MM-DD)
- Consistent session format (YY/YY)
- Standard abbreviations

### 5. Complete Addresses
- More complete = better geocoding
- Include postcode for accuracy
- Specify state/province

### 6. Provide Internship Dates
- Include both start and end dates for students
- Helps with internship tracking and planning
- Format: YYYY-MM-DD

## 🔐 Security

### Passwords
- Auto-generated unique passwords
- Sent only via email (not displayed)
- Users should change on first login

### Data Privacy
- Only coordinators/admins can bulk register
- Email notifications are private
- Logs track who uploaded what

## 🆘 Troubleshooting

### "CSV must contain studentID or lecturerID"
- Check header row spelling
- Ensure first row is headers
- Use exact field names

### "Email already exists"
- Check for duplicate emails in your CSV
- Verify email not already in system
- Remove duplicate row

### "Student ID already exists"
- Check for duplicate IDs in your CSV
- Verify ID not already in system
- Use unique ID for each user

### "Invalid email format"
- Check email has @ symbol
- No spaces in email
- Valid domain (.com, .edu, etc.)

### "Invalid session format"
- Use YY/YY format (e.g., 24/25)
- Not YYYY/YYYY or YYYY format
- Check for typos

### "Course code too long"
- Maximum 8 characters
- Trim extra characters
- Check for spaces

### "Internship end date must be after start date"
- Verify end date is later than start date
- Check date format (YYYY-MM-DD)
- Ensure both dates are provided

### No email received
- Check spam folder
- Verify email address is correct
- Check mail server logs

### Geocoding failed
- Check Google Maps API key configured
- Verify address is complete
- Try providing coordinates manually

## 📞 Support

For issues:
1. Check this guide
2. Review error messages
3. Test with template file
4. Contact system administrator

## 🎓 Examples

### Complete Student CSV (All fields)
```csv
studentID,name,email,phone,address,city,postcode,state,country,nationality,program,academicAdvisorID
CD220001,Ahmad Bin Ali,ahmad.ali@example.com,0123456789,No 123 Jalan Utama,Kuantan,26000,Pahang,Malaysia,Malaysian,BCS,LEC001
CD220002,Siti Fatimah,siti.fatimah@example.com,0198765432,No 456 Jalan Permai,Kuala Lumpur,50000,Wilayah Persekutuan,Malaysia,Malaysian,BCN,LEC002
```

**Note**: Course Code, Session, and Internship Dates are entered in the upload form.

### Complete Lecturer CSV (All fields)
```csv
lecturerID,name,email,staffGrade,role,position,department,isAcademicAdvisor,is_supervisor,supervisor_quota
LEC001,Dr. Abdullah Rahman,abdullah.rahman@example.com,DG54,Lecturer,Senior Lecturer,Faculty of Computing,true,true,5
LEC002,Prof. Dr. Noor Azlina,noor.azlina@example.com,VK7,Professor,Professor,Faculty of Computing,true,true,8
```

**Note**: Session is entered in the upload form.

---

**Last Updated**: January 2026
**System**: Internlink - Industrial Training Management System
**Version**: 2.0

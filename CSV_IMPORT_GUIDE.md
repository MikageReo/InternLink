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
4. Select **Semester** and **Year**
5. Choose your CSV file
6. Click **Upload**

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
| program | Degree program | Bachelor of Computer Science (Software Engineering) |
| latitude | GPS latitude (leave empty for auto-geocode) | 3.8077 |
| longitude | GPS longitude (leave empty for auto-geocode) | 103.3260 |

### Student CSV Example
```csv
studentID,name,email,phone,address,city,postcode,state,country,nationality,program,latitude,longitude
CD220001,Ahmad Bin Ali,ahmad.ali@example.com,0123456789,No 123 Jalan Utama,Kuantan,26000,Pahang,Malaysia,Malaysian,Bachelor of Computer Science (Software Engineering),,
CD220002,Siti Fatimah,siti.fatimah@example.com,0198765432,No 456 Jalan Permai,Kuala Lumpur,50000,Wilayah Persekutuan,Malaysia,Malaysian,Bachelor of Computer Science (Computer Systems & Networking),,
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

## 🔍 Important Notes

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

### Computer Science Programs
- Bachelor of Computer Science (Software Engineering)
- Bachelor of Computer Science (Computer Systems & Networking)
- Bachelor of Computer Science (Graphics & Multimedia)
- Bachelor of Computer Science (Data Science)

### Engineering Programs
- Bachelor of Electrical Engineering
- Bachelor of Mechanical Engineering
- Bachelor of Civil Engineering
- Bachelor of Chemical Engineering

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

## 🔧 System Behavior

### Auto-Generated
- **User Account**: Created automatically
- **Password**: Unique random password (sent via email)
- **User Role**: Set based on file type (student/lecturer)
- **Status**: Set to 'active' by default
- **Semester/Year**: Set from upload form

### Auto-Detection
- **File Type**: Detected by header (studentID = students, lecturerID = lecturers)
- **Geocoding**: Attempts to geocode addresses automatically if coordinates not provided

### Validation
System checks:
- ✅ Unique student/lecturer ID
- ✅ Unique email address
- ✅ Valid email format
- ✅ Required fields not empty

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
- Use same date format throughout
- Consistent capitalization
- Standard abbreviations

### 5. Complete Addresses
- More complete = better geocoding
- Include postcode for accuracy
- Specify state/province

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

### Minimal Student CSV (Required fields only)
```csv
studentID,name,email
CD220001,Ahmad Bin Ali,ahmad.ali@example.com
CD220002,Siti Fatimah,siti.fatimah@example.com
```

### Complete Student CSV (All fields)
```csv
studentID,name,email,phone,address,city,postcode,state,country,nationality,program
CD220001,Ahmad Bin Ali,ahmad.ali@example.com,0123456789,No 123 Jalan Utama,Kuantan,26000,Pahang,Malaysia,Malaysian,Bachelor of Computer Science
```

### Minimal Lecturer CSV (Required fields only)
```csv
lecturerID,name,email
LEC001,Dr. Abdullah Rahman,abdullah.rahman@example.com
LEC002,Prof. Dr. Noor Azlina,noor.azlina@example.com
```

### Supervisor CSV (With supervisor fields)
```csv
lecturerID,name,email,department,is_supervisor,supervisor_quota
LEC001,Dr. Abdullah Rahman,abdullah.rahman@example.com,Faculty of Computing,true,5
LEC002,Prof. Dr. Noor Azlina,noor.azlina@example.com,Faculty of Computing,true,8
LEC003,Dr. Muhammad Faiz,faiz.muhammad@example.com,Faculty of Computing,true,10
```

---

**Last Updated**: November 2025
**System**: Internlink - Industrial Training Management System
**Version**: 1.0


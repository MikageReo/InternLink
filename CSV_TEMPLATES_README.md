# 📋 CSV Bulk Registration Templates

## ✅ What's Been Created

### 1. CSV Template Files
Located in `csv_templates/` folder:

**📄 students_bulk_registration_template.csv**
- Ready-to-use template for student bulk registration
- Includes 3 example students
- Shows all available fields with sample data

**📄 lecturers_bulk_registration_template.csv**
- Ready-to-use template for lecturer bulk registration
- Includes 3 example lecturers
- Shows all available fields including **new supervisor fields**

### 2. Documentation Files

**📘 CSV_IMPORT_GUIDE.md** (Comprehensive Guide)
- Complete field descriptions
- Field-by-field explanations
- Examples and best practices
- Troubleshooting section
- Common mistakes to avoid

**📙 CSV_QUICK_REFERENCE.md** (Quick Reference)
- One-page cheat sheet
- Common examples
- Quick field lookup
- Pro tips

**📗 CSV_TEMPLATES_README.md** (This File)
- Overview of all CSV resources
- How to get started

---

## 🚀 How to Use

### Quick Start

1. **Open Template**
   ```
   csv_templates/students_bulk_registration_template.csv
   or
   csv_templates/lecturers_bulk_registration_template.csv
   ```

2. **Edit in Excel/Google Sheets**
   - Delete example rows (keep header!)
   - Add your real data
   - Save as CSV format

3. **Upload**
   - Login to system
   - Go to User Directory
   - Click "Bulk Registration"
   - Select Semester & Year
   - Upload your CSV file
   - Done! ✅

---

## 📊 Template Comparison

### Students Template
```csv
studentID,name,email,phone,address,city,postcode,state,country,nationality,program
```
**Use for:** Registering students in bulk

### Lecturers Template
```csv
lecturerID,name,email,staffGrade,role,position,address,city,postcode,state,country,
researchGroup,department,studentQuota,isAcademicAdvisor,isSupervisorFaculty,
isCommittee,isCoordinator,isAdmin,is_supervisor,supervisor_quota
```
**Use for:** Registering lecturers/supervisors in bulk

---

## 🆕 New Features Added

### For Supervisor Assignment System

The lecturer template now includes:

✅ **`is_supervisor`** field
- Set to `true` to enable placement supervision
- Allows lecturer to be assigned to students

✅ **`supervisor_quota`** field  
- Maximum number of students for placement supervision
- Recommended: 5-10 students per supervisor

**Example:**
```csv
lecturerID,name,email,department,is_supervisor,supervisor_quota
LEC001,Dr. Ahmad,ahmad@example.com,Computing,true,5
```

This integrates with the **Supervisor Assignment** feature!

---

## 📂 File Structure

```
Internlink/
├── csv_templates/
│   ├── students_bulk_registration_template.csv     ← Students template
│   └── lecturers_bulk_registration_template.csv    ← Lecturers template
├── CSV_IMPORT_GUIDE.md                              ← Full documentation
├── CSV_QUICK_REFERENCE.md                           ← Quick cheat sheet
└── CSV_TEMPLATES_README.md                          ← This file
```

---

## 🎯 Common Use Cases

### 1. New Semester Registration
```
1. Download students template
2. Fill with new student data
3. Upload with semester & year
4. Students receive login emails
```

### 2. Adding New Lecturers
```
1. Download lecturers template
2. Fill with lecturer data
3. Set supervisor roles if needed
4. Upload to system
```

### 3. Setting Up Supervisors
```
1. Use lecturers template
2. Set is_supervisor = true
3. Set supervisor_quota (e.g., 5-10)
4. Include complete address
5. Upload
6. Run: php artisan geocode:existing-data --type=lecturers
```

---

## 📋 Field Reference Tables

### Student Fields (Required ⭐)

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| studentID | ⭐ | Unique ID | CD220001 |
| name | ⭐ | Full name | Ahmad Ali |
| email | ⭐ | Email address | ahmad@example.com |
| phone | | Contact number | 0123456789 |
| address | | Street address | Jalan Utama |
| city | | City | Kuantan |
| postcode | | Postal code | 26000 |
| state | | State | Pahang |
| country | | Country | Malaysia |
| nationality | | Nationality | Malaysian |
| program | | Degree program | Bachelor of Computer Science |
| latitude | | GPS latitude | 3.8077 |
| longitude | | GPS longitude | 103.3260 |

### Lecturer Fields (Required ⭐)

| Field | Required | Description | Example |
|-------|----------|-------------|---------|
| lecturerID | ⭐ | Unique ID | LEC001 |
| name | ⭐ | Full name | Dr. Ahmad |
| email | ⭐ | Email address | ahmad@example.com |
| department | | Department | Faculty of Computing |
| **is_supervisor** | | Can supervise placements | true |
| **supervisor_quota** | | Max placement students | 5 |
| isAcademicAdvisor | | Academic advisor | true |
| isCoordinator | | Program coordinator | true |
| isCommittee | | Committee member | true |
| isAdmin | | System admin | false |

---

## 💡 Tips for Success

### ✅ DO
- ✅ Keep the header row
- ✅ Use lowercase `true`/`false` for boolean fields
- ✅ Save as CSV format (.csv)
- ✅ Use unique IDs and emails
- ✅ Test with 2-3 users first
- ✅ Include complete addresses for geocoding
- ✅ Double-check email addresses

### ❌ DON'T
- ❌ Delete the header row
- ❌ Use `yes`/`no`, `1`/`0` for boolean fields
- ❌ Save as Excel (.xlsx)
- ❌ Duplicate IDs or emails
- ❌ Leave required fields empty
- ❌ Add extra spaces around values

---

## 🔄 Integration with Other Features

### Supervisor Assignment
When you register lecturers with:
- `is_supervisor = true`
- `supervisor_quota > 0`
- Complete address information

They will be available in the **Supervisor Assignment** system for:
- Distance-based recommendations
- Automatic assignment
- Quota management

### Geocoding
After uploading users with addresses:
```bash
# Geocode all data
php artisan geocode:existing-data

# Or specific type
php artisan geocode:existing-data --type=students
php artisan geocode:existing-data --type=lecturers
```

---

## 📧 Email Notifications

After successful upload, each user receives an email with:
- ✉️ Their username (email)
- 🔑 Auto-generated password
- 🔗 Login URL
- 📖 Getting started instructions

**Note:** Users should change their password after first login.

---

## 🆘 Troubleshooting

### Template Not Found?
Check the `csv_templates/` folder exists in your project root.

### Upload Fails?
- Verify CSV format (not Excel)
- Check required fields are filled
- Ensure no duplicate IDs/emails
- Review error messages

### No Emails Sent?
- Check email server configuration
- Verify SMTP settings in .env
- Check spam folder
- Review Laravel logs

### Geocoding Not Working?
- Ensure Google Maps API key configured
- Run geocoding command after upload
- Check addresses are complete
- Review logs for API errors

---

## 📚 Additional Resources

- **Full Guide**: Read `CSV_IMPORT_GUIDE.md` for detailed instructions
- **Quick Reference**: Check `CSV_QUICK_REFERENCE.md` for quick lookup
- **Supervisor Guide**: See `SUPERVISOR_ASSIGNMENT_GUIDE.md` for supervisor features
- **System Docs**: See `SETUP_COMPLETE_SUMMARY.md` for overall system status

---

## 🎓 Example Scenarios

### Scenario 1: Register 50 New Students
```
1. Open: csv_templates/students_bulk_registration_template.csv
2. Delete example rows
3. Add 50 student records
4. Save as CSV
5. Upload via User Directory → Bulk Registration
6. Select current semester & year
7. Upload file
8. Check results: "50 users created successfully"
9. Students receive email with login details
```

### Scenario 2: Add 10 Supervisors
```
1. Open: csv_templates/lecturers_bulk_registration_template.csv
2. Fill 10 lecturer records
3. Set is_supervisor = true
4. Set supervisor_quota = 5-10
5. Include complete addresses
6. Upload via Bulk Registration
7. Run: php artisan geocode:existing-data --type=lecturers
8. Supervisors ready for assignment!
```

### Scenario 3: Mixed Roles Lecturer
```
Create a lecturer who is:
- Academic Advisor
- Placement Supervisor
- Committee Member

CSV row:
lecturerID,name,email,department,isAcademicAdvisor,is_supervisor,supervisor_quota,isCommittee
LEC001,Dr. Ahmad,ahmad@example.com,Computing,true,true,5,true
```

---

## ✅ System Updates

### What Was Updated Today

**Updated File:**
- `app/Livewire/UserDirectoryTable.php`
  - Added `is_supervisor` field support in CSV import
  - Added `supervisor_quota` field support in CSV import
  - Now integrates with Supervisor Assignment feature

**New Files Created:**
- `csv_templates/students_bulk_registration_template.csv`
- `csv_templates/lecturers_bulk_registration_template.csv`
- `CSV_IMPORT_GUIDE.md`
- `CSV_QUICK_REFERENCE.md`
- `CSV_TEMPLATES_README.md`

---

## 🎉 You're Ready!

Everything is set up and ready to use:

1. ✅ Templates created
2. ✅ Documentation complete
3. ✅ System updated with supervisor fields
4. ✅ Integration with supervisor assignment
5. ✅ User Directory page fixed

**Start importing your users now!** 🚀

---

**Created:** November 2025  
**System:** Internlink - Industrial Training Management System  
**Feature:** Bulk User Registration via CSV


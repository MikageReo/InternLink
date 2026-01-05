# 📋 CSV Bulk Registration - Quick Reference

## 🚀 3-Step Process

1. **Download Template** → `csv_templates/` folder
2. **Edit & Fill Data** → Add your users
3. **Upload** → User Directory → Bulk Registration

---

## 📊 Students CSV

### Minimum Required
```csv
studentID,name,email
CD220001,Ahmad Ali,ahmad@example.com
```

### Recommended (with addresses and session)
```csv
studentID,name,email,phone,address,city,postcode,state,country,program,session,internship_start_date,internship_end_date
CD220001,Ahmad Ali,ahmad@example.com,0123456789,Jalan Utama,Kuantan,26000,Pahang,Malaysia,BCS,24/25,2024-06-01,2024-12-31
```

### All Fields
```
studentID, name, email, phone, address, city, postcode, state, country, 
nationality, program, academicAdvisorID, latitude, longitude
```

### ⚠️ Important: Form Fields (NOT in CSV)
These fields are entered in the upload form and applied to ALL students:
- **Session** (YY/YY format, e.g., 24/25)
- **Course Code** (optional, max 8 characters)
- **Internship Start Date** (YYYY-MM-DD)
- **Internship End Date** (YYYY-MM-DD)

---

## 👨‍🏫 Lecturers CSV

### Minimum Required
```csv
lecturerID,name,email
LEC001,Dr. Abdullah,abdullah@example.com
```

### For Supervisors (Recommended)
```csv
lecturerID,name,email,department,address,city,state,session,is_supervisor,supervisor_quota
LEC001,Dr. Abdullah,abdullah@example.com,Computing,Faculty of Computing,Kuantan,Pahang,24/25,true,5
```

### All Fields
```
lecturerID, name, email, staffGrade, role, position, address, city, postcode, 
state, country, researchGroup, department, program, studentQuota, 
isAcademicAdvisor, isSupervisorFaculty, isCommittee, isCoordinator, isAdmin, 
is_supervisor, supervisor_quota, latitude, longitude
```

### ⚠️ Important: Form Fields (NOT in CSV)
These fields are entered in the upload form and applied to ALL lecturers:
- **Session** (YY/YY format, e.g., 24/25)

---

## ✅ Field Rules

| Field | Rule |
|-------|------|
| **studentID** / **lecturerID** | Required, unique |
| **name** | Required |
| **email** | Required, unique, valid format |
| **Boolean fields** | Use lowercase `true` or `false` |
| **Numbers** | No quotes, plain numbers |
| **Coordinates** | Leave empty for auto-geocode |

---

## 💼 Lecturer Roles Reference

| Field | Purpose | Value |
|-------|---------|-------|
| `isAcademicAdvisor` | Academic mentoring | true/false |
| `is_supervisor` | Placement supervision | true/false |
| `supervisor_quota` | Max placement students | 0-20 |
| `isCoordinator` | Program coordinator | true/false |
| `isCommittee` | Committee member | true/false |
| `isAdmin` | System admin | true/false |

**Example Supervisor:**
```csv
LEC001,Dr. Ahmad,ahmad@example.com,Computing,24/25,true,5,true,8
```
↑ Academic Advisor (quota: 5) + Supervisor (quota: 8)

---

## 🎯 Common Programs

**Program Codes:**
- **BCS**: Bachelor of Computer Science (Software Engineering) with Honours
- **BCN**: Bachelor of Computer Science (Computer Systems & Networking) with Honours
- **BCM**: Bachelor of Computer Science (Multimedia Software) with Honours
- **BCY**: Bachelor of Computer Science (Cyber Security) with Honours
- **DRC**: Diploma in Computer Science

---

## ⚠️ Common Errors

| Error | Solution |
|-------|----------|
| "Must contain studentID/lecturerID" | Check header row spelling |
| "Email already exists" | Use unique email for each user |
| "ID already exists" | Use unique ID for each user |
| "Invalid email format" | Check @ symbol and domain |
| "Invalid session format" | Use YY/YY format (e.g., 24/25) |
| "Course code too long" | Maximum 8 characters |
| "End date must be after start date" | Check internship dates |

---

## 💡 Pro Tips

1. ✅ **Start Small** - Test with 2-3 users first
2. ✅ **Complete Addresses** - Better geocoding for supervisor assignment
3. ✅ **Save as CSV** - Not .xlsx or .xls
4. ✅ **Keep Header Row** - Don't delete it
5. ✅ **No Extra Spaces** - Trim all values
6. ✅ **Backup Original** - Keep a copy
7. ✅ **Use Session Format** - YY/YY (e.g., 24/25) instead of year
8. ✅ **Provide Internship Dates** - Helps with tracking (YYYY-MM-DD format)

---

## 📍 For Supervisor Assignment

To enable distance-based supervisor recommendations:

**Students:**
```csv
studentID,name,email,address,city,postcode,state,country
CD220001,Ahmad,ahmad@example.com,Jalan Utama,Kuantan,26000,Pahang,Malaysia
```
*Note: Session is entered in the upload form*

**Lecturers:**
```csv
lecturerID,name,email,department,address,city,state,is_supervisor,supervisor_quota
LEC001,Dr. Ahmad,ahmad@example.com,Computing,Faculty Building,Kuantan,Pahang,true,5
```
*Note: Session is entered in the upload form*

After upload, run:
```bash
php artisan geocode:existing-data
```

---

## 🔐 What Happens After Upload

1. ✅ User accounts created
2. ✅ Random passwords generated
3. ✅ Emails sent with credentials
4. ✅ Addresses geocoded (if API configured)
5. ✅ Session converted from year if needed
6. ✅ Users can login immediately

---

## 📁 File Locations

- **Templates**: `csv_templates/`
- **Full Guide**: `CSV_IMPORT_GUIDE.md`
- **Upload Page**: User Directory → Bulk Registration button

---

## 📞 Need Help?

1. Check full guide: `CSV_IMPORT_GUIDE.md`
2. Use provided templates
3. Test with small batch first
4. Review error messages carefully

**Ready to go!** 🚀

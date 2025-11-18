# 🤖 Auto Supervisor Assignment - AI-Powered Recommendations

## ✅ Feature Complete!

An intelligent supervisor recommendation system that uses hybrid scoring to match supervisors with students based on coursework, travel preference, proximity, and workload.

---

## 🎯 How It Works

### **Scoring Algorithm**

For each student, the system calculates a match score for every available supervisor using:

```
Total Score = (Course Match × 40%) + (Travel Preference × 30%) + (Distance × 20%) + (Workload × 10%)
```

#### **Score Components:**

1. **Course Match (40% weight)**
   - **1.0** = Student's program matches lecturer's preferred coursework
   - **0.7** = Partial match (keywords in job scope match)
   - **0.0** = No match

2. **Travel Preference Match (30% weight)**
   - **Local** preference: 1.0 if distance ≤ 50km, else 0.0
   - **Nationwide** preference: 1.0 (accepts any distance)

3. **Distance Score (20% weight)**
   - Formula: `1 / (1 + distance_in_km)`
   - Closer = Higher score

4. **Workload Score (10% weight)**
   - Formula: `1 - (current_assignments / max_quota)`
   - Less loaded = Higher score

---

## 🚀 How to Use

### **Step 1: Access the Feature**

**URL:** `http://your-domain/lecturer/auto-supervisor-assignments`

**Who can access:** Coordinators only

**Menu:** Dashboard → Auto Supervisor Assignment

### **Step 2: View Students Awaiting Assignment**

You'll see:
- All students with accepted placements but no supervisor
- Student name, program, company, and job scope
- Statistics dashboard

### **Step 3: Get AI Recommendations**

1. Click **"Get Recommendations"** button next to any student
2. System analyzes all available supervisors
3. Shows **Top 3 recommended supervisors** with:
   - Overall match score (0-100%)
   - Score breakdown by component
   - Distance from placement location
   - Current workload
   - Travel preference
   - Expertise area

### **Step 4: Review Recommendations**

Each recommended supervisor shows:

**🥇 Rank Badge** (#1 Gold, #2 Silver, #3 Bronze)

**📊 Score Breakdown:**
- Course Match (40%)
- Travel Preference (30%)
- Distance (20%)
- Workload (10%)

**📍 Key Details:**
- Distance to placement location
- Travel preference (local/nationwide)
- Current workload (X/Y students)
- Expertise/preferred coursework

### **Step 5: Assign Supervisor**

1. Review all 3 recommendations
2. Optionally add notes
3. Click **"Assign as Supervisor"** on your chosen recommendation
4. System automatically:
   - Assigns supervisor to student
   - Updates lecturer's assignment count
   - Logs recommendation data
   - Sends email notification to student
   - Adds AI recommendation note

---

## 📋 Prerequisites

### **1. Lecturers Must Have:**

✅ `isSupervisorFaculty` = TRUE  
✅ `supervisor_quota` > 0  
✅ `status` = Active  
✅ `preferred_coursework` (recommended)  
✅ `travel_preference` (local or nationwide)  
✅ Complete address + geocoded location  
✅ Same department as student

### **2. Students Must Have:**

✅ Accepted placement application  
✅ Company address (for distance calculation)  
✅ Program information  
✅ Job scope information

### **3. System Requirements:**

✅ Google Maps API configured  
✅ Geocoding completed for lecturers and placements  
✅ Supervisor quota system active

---

## 🛠️ Setup Guide

### **Step 1: Add New Fields to Lecturers**

The migration has already been run, adding:
- `preferred_coursework` (string, nullable)
- `travel_preference` (enum: 'local', 'nationwide')

### **Step 2: Update Lecturer Data**

**Via CSV Import:**
Use the updated template: `csv_templates/lecturers_bulk_registration_template.csv`

**New fields:**
```csv
preferred_coursework,travel_preference
Software Engineering,local
Computer Science,nationwide
```

**Via User Directory:**
Edit lecturers individually and set:
- Preferred Coursework (e.g., "Software Engineering", "Data Science")
- Travel Preference (local or nationwide)

### **Step 3: Geocode Addresses**

Ensure all lecturers and placements have geocoded addresses:

```bash
php artisan geocode:existing-data --type=lecturers
php artisan geocode:existing-data --type=placements
```

### **Step 4: Access the Feature**

Navigate to: `/lecturer/auto-supervisor-assignments`

---

## 🎨 Features

### **✨ AI-Powered Recommendations**
- Hybrid scoring algorithm
- Considers multiple factors
- Ranks supervisors by best match

### **📊 Transparent Scoring**
- Visual score breakdown
- Shows exact weights
- Explains each component

### **📝 Automatic Documentation**
- Logs all recommendations
- Records assigned score
- Tracks AI decisions

### **🎯 Smart Filtering**
- Only shows available supervisors
- Respects quota limits
- Enforces department matching
- Checks travel preferences

### **📧 Email Notifications**
- Students notified automatically
- Includes recommendation details
- Professional formatting

---

## 💡 Best Practices

### **1. Keep Lecturer Profiles Updated**

✅ Set accurate `preferred_coursework`  
✅ Choose appropriate `travel_preference`  
✅ Maintain current address  
✅ Keep quota up to date

### **2. Interpret Scores Wisely**

- **80%+** = Excellent match
- **60-79%** = Good match
- **40-59%** = Moderate match
- **<40%** = Poor match

### **3. Review All 3 Recommendations**

Don't always pick #1! Consider:
- Supervisor's teaching style
- Student's specific needs
- Special circumstances
- Previous relationships

### **4. Add Notes**

Use the notes field to document:
- Why you chose this supervisor
- Special considerations
- Student requests
- Override reasons

---

## 📖 Example Scenarios

### **Scenario 1: Perfect Match**

**Student:**
- Program: Bachelor of Software Engineering
- Company: Tech startup in Kuantan
- Job Scope: Mobile app development

**Top Recommendation:**
- Dr. Ahmad (Score: 92%)
- Expertise: Software Engineering ✅
- Location: Kuantan (5km away) ✅
- Travel Pref: Local ✅
- Workload: 2/8 students ✅

**Result:** Excellent match on all criteria!

---

### **Scenario 2: Nationwide Supervisor**

**Student:**
- Program: Bachelor of Computer Science
- Company: Remote company (300km away)
- Job Scope: Web development

**Top Recommendation:**
- Prof. Noor (Score: 85%)
- Expertise: Computer Science ✅
- Location: 300km away ⚠️
- Travel Pref: Nationwide ✅
- Workload: 3/10 students ✅

**Result:** Good match! Distance doesn't matter due to nationwide preference.

---

### **Scenario 3: Limited Options**

**Student:**
- Program: Bachelor of Data Science
- Company: Data analytics firm
- Job Scope: Machine learning

**Top Recommendation:**
- Dr. Sarah (Score: 58%)
- Expertise: Mathematics ⚠️
- Location: 15km away ✅
- Travel Pref: Local ✅
- Workload: 7/8 students ⚠️

**Result:** Moderate match. May need to add notes explaining why this is the best available option.

---

## 🔍 Understanding the UI

### **Student List**
- **Green icon** = Already assigned
- **Yellow icon** = Awaiting assignment
- Shows company and job scope for context

### **Recommendation Modal**
- **Blue header** = Student information
- **Rank badges** = #1 Gold, #2 Silver, #3 Bronze
- **Color-coded scores:**
  - Green (80%+) = Excellent
  - Blue (60-79%) = Good
  - Yellow (40-59%) = Moderate
  - Gray (<40%) = Poor

### **Score Breakdown Boxes**
- **Blue** = Course Match (40%)
- **Green** = Travel Preference (30%)
- **Purple** = Distance (20%)
- **Orange** = Workload (10%)

---

## 🆘 Troubleshooting

### **"No recommendations available"**

**Possible causes:**
1. All supervisors at full capacity
   - **Solution:** Increase supervisor quotas
2. No supervisors in student's department
   - **Solution:** Add more supervisors or enable cross-department
3. Missing location data
   - **Solution:** Run geocoding command
4. No supervisors marked as `isSupervisorFaculty`
   - **Solution:** Update lecturer profiles

### **Scores seem low**

**Check:**
- Are `preferred_coursework` fields set?
- Is travel preference appropriate?
- Are addresses geocoded correctly?
- Is student's program matching lecturer departments?

### **Distance not showing**

**Run:**
```bash
php artisan geocode:existing-data
```

Ensure both lecturer and company have valid addresses.

---

## 📊 Recommendation Logs

All recommendations are logged for transparency:

**Location:** `storage/logs/laravel.log`

**Log entry includes:**
- Student ID and name
- Student program
- Top 3 recommended lecturers
- Each lecturer's score and distance
- Timestamp

**Search logs:**
```bash
grep "Supervisor recommendations generated" storage/logs/laravel.log
```

---

## 🎓 Technical Details

### **Files Created:**

1. **Migration:** `database/migrations/2025_11_11_080850_add_recommendation_fields_to_lecturers_table.php`
2. **Service:** `app/Services/SupervisorRecommendationService.php`
3. **Livewire:** `app/Livewire/AutoSupervisorAssignment.php`
4. **View:** `resources/views/livewire/auto-supervisor-assignment.blade.php`
5. **Page:** `resources/views/lecturer/dashboard/autoSupervisorAssignments.blade.php`
6. **Route:** Added to `routes/web.php`

### **Database Changes:**

**Lecturers table:**
- `preferred_coursework` (string, nullable)
- `travel_preference` (enum: 'local', 'nationwide', default: 'local')

### **CSV Import:**

Updated template includes new fields:
- `preferred_coursework`
- `travel_preference`

---

## ✅ System Status

| Component | Status |
|-----------|--------|
| Database Migration | ✅ Complete |
| Recommendation Service | ✅ Complete |
| Livewire Component | ✅ Complete |
| UI Interface | ✅ Complete |
| Route Configuration | ✅ Complete |
| CSV Import Support | ✅ Complete |
| Logging System | ✅ Complete |
| Email Notifications | ✅ Complete |

---

## 🚀 Ready to Use!

The Auto Supervisor Assignment feature is fully operational and ready for use.

**Access it now:**
`http://your-domain/lecturer/auto-supervisor-assignments`

**Thank you for using the AI-Powered Supervisor Assignment System!** 🎉

---

**Created:** November 2025  
**System:** Internlink - Industrial Training Management System  
**Feature:** Auto Supervisor Assignment with AI Recommendations



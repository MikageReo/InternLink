# Supervisor Assignment Scoring System Guide

## Overview

The supervisor assignment system uses a **hybrid scoring algorithm** to recommend the best supervisors for students. The system calculates a weighted score based on multiple factors to ensure optimal matching between students and supervisors.

---

## Eligibility Requirements

Before a lecturer can be considered for supervisor assignment, they must meet ALL of the following criteria:

### ✅ Required Criteria:
1. **Is Supervisor Faculty**: `isSupervisorFaculty = true`
2. **Active Status**: `status = 'active'`
3. **No Administrative Position**: Must NOT hold positions like:
   - Dean
   - Deputy Dean
   - Director
   - Deputy Director
   - Head of Department
   - Deputy Head of Department
   - Chairperson
   - Deputy Chairperson
   - Vice Chancellor
   - Deputy Vice Chancellor
   - Registrar
   - Deputy Registrar
4. **Available Quota**: `(supervisor_quota - current_assignments) > 0` (unless quota override is enabled)

### ❌ Exclusion Rules:
- **Inactive lecturers** (any status other than 'active') → **Cannot supervise**
- **Active lecturers with administrative positions** → **Cannot supervise**
- **Lecturers at quota limit** (unless override) → **Cannot supervise**

---

## Scoring Formula

The total score is calculated using the following weighted formula:

```
Total Score = (Course Match × 40%) + (Preference Match × 30%) + (Distance Score × 20%) + (Workload Score × 10%)
```

### Score Breakdown:

| Factor | Weight | Description | Range |
|--------|--------|-------------|-------|
| **Course Match** | 40% | Match between student's program and supervisor's preferred coursework | 0.0 - 1.0 |
| **Preference Match** | 30% | Match between student location and supervisor's travel preference | 0.0 - 1.0 |
| **Distance Score** | 20% | Proximity between supervisor and student's placement location | 0.0 - 1.0 |
| **Workload Score** | 10% | Supervisor's current workload vs. maximum capacity | 0.0 - 1.0 |

**Maximum Possible Score**: 1.0 (100%)

1. Course Match (40% - Highest Priority)

Rationale: Academic alignment is the most critical factor for successful supervision.

Justification:
  - Quality of Supervision: Supervisors with expertise in the student's field can provide better guidance, technical support, and industry insights relevant to the student's program.
  - Academic Integrity: Ensures that students are supervised by faculty members who understand their field of study, maintaining academic standards and program requirements.
  - Student Success: Research shows that supervisor-student alignment in expertise significantly impacts student learning outcomes and project success.
  - Institutional Priority: The institution prioritizes academic excellence and proper mentorship over convenience factors.

Impact:
  - A perfect course match (1.0) contributes 40 points to the total score
  - A partial match (0.7) contributes 28 points
  - No match (0.0) contributes 0 points, making it unlikely for supervisors without relevant expertise to be selected
Stakeholder Input: This weight reflects the consensus that academic expertise is non-negotiable for quality supervision.
---
2. Preference Match (30% - Second Priority)

Rationale: Respecting supervisor constraints and preferences is essential for sustainable supervision relationships.

Justification:
-  Supervisor Satisfaction: Supervisors who can work within their preferred travel range are more likely to be engaged and available for regular visits and support.
-  Practical Constraints: Supervisors with "local" preference may have:
  - Personal commitments (family, health)
  - Professional obligations (teaching schedules, research commitments)
  - Transportation limitations
  - Time constraints
-  Prevention of Conflicts: Assigning supervisors outside their preference range can lead to:
  - Reduced visit frequency
  - Supervisor dissatisfaction
  - Potential reassignment requests
-  Flexibility Recognition: Supervisors with "nationwide" preference demonstrate flexibility and should be recognized for their willingness to travel.

Impact:
-  A preference match (1.0) contributes 30 points to the total score
-  A mismatch (0.0) for local supervisors with distant students contributes 0 points, effectively excluding them from consideration

3. Distance Score (20% - Third Priority)

Rationale: Proximity is a practical consideration that affects supervision quality and accessibility.

Justification:
-  Visit Frequency: Closer supervisors can visit students more frequently, providing better oversight and support.
-  Emergency Response: In case of urgent issues, closer supervisors can respond more quickly.
-  Cost Efficiency: Reduces travel costs and time for both supervisors and the institution.
-  Accessibility: Easier for supervisors to conduct site visits, meet with industry supervisors, and attend student presentations.
-  Secondary to Quality: While important, distance should not override academic expertise or supervisor preferences.

Impact:
-  Distance score uses a diminishing returns formula: 1 / (1 + distance_km)
-  A supervisor 10 km away contributes approximately 1.8 points
-  A supervisor 200 km away contributes approximately 0.1 points
-  The 20% weight ensures distance influences ranking without dominating it


4. Workload Score (10% - Lowest Priority)

Rationale: Fair distribution of supervision load ensures quality and prevents supervisor burnout.

Justification:
-  Quality Maintenance: Overloaded supervisors may struggle to provide adequate attention to all students.
-  Fairness: Distributes supervision responsibilities equitably among available supervisors.
-  Prevention of Burnout: Prevents individual supervisors from being overwhelmed.
-  Secondary Consideration: Workload balance is important but should not override matching quality.

Impact:
-  A supervisor with 20% capacity used contributes 8 points
-  A supervisor with 80% capacity used contributes 2 points
-  A supervisor at full capacity contributes 0 points (and is excluded unless quota override is enabled)

## Weight Rationale

### Course Match (40% - Highest Priority)
**Rationale**: Academic alignment is the most critical factor for successful supervision.
- Ensures supervisors have relevant expertise
- Matches student's field of study with supervisor's specialization
- Foundation for quality mentorship

### Preference Match (30% - Second Priority)
**Rationale**: Respects supervisor constraints and preferences.
- Supervisors with "local" preference may have personal/professional constraints
- "Nationwide" supervisors are more flexible
- Prevents assignment conflicts and improves satisfaction

### Distance Score (20% - Third Priority)
**Rationale**: Practical consideration for supervision visits.
- Closer supervisors can visit more frequently
- Reduces travel time and costs
- Improves accessibility for both parties

### Workload Score (10% - Lowest Priority)
**Rationale**: Ensures fair distribution of supervision load.
- Prevents overloading individual supervisors
- Maintains quality of supervision
- Secondary to matching quality

## Detailed Scoring Components

### 1. Course Match (40% Weight) - **HIGHEST PRIORITY**

**Purpose**: Match students with supervisors who have expertise in the student's field of study.

**Calculation**:
- **Perfect Match (1.0)**: Student's program matches supervisor's `preferred_coursework`
  - Uses case-insensitive partial matching
  - Example: "Computer Science" matches "Computer Science" or "CS"
  
- **Partial Match (0.7)**: Supervisor's preferred coursework keywords found in student's job scope
  - Only keywords longer than 3 characters are considered
  - Example: "Software Engineering" in job scope matches supervisor with "Software" preference
  
- **No Match (0.0)**: No connection found

**Weighted Score**: `Course Match × 0.4`

**Example**:
```
Student Program: "Computer Science"
Supervisor Preferred Coursework: "Computer Science"
→ Course Match: 1.0
→ Weighted Score: 1.0 × 0.4 = 0.4 (40 points)
```

---

### 2. Preference Match (30% Weight) - **SECOND PRIORITY**

**Purpose**: Respect supervisor's travel preference (local vs. nationwide).

**Calculation**:
- **Local Preference** (`travel_preference = 'local'`):
  - Distance ≤ 50 km → **1.0** (Perfect match)
  - Distance > 50 km → **0.0** (No match)
  
- **Nationwide Preference** (`travel_preference = 'nationwide'`):
  - Any distance → **1.0** (Always accepts)
  
- **Unknown Distance** (coordinates missing):
  - Returns **0.5** (Neutral score)

**Weighted Score**: `Preference Match × 0.3`

**Example**:
```
Supervisor Travel Preference: "local"
Distance to Student: 35 km
→ Preference Match: 1.0 (within 50 km)
→ Weighted Score: 1.0 × 0.3 = 0.3 (30 points)
```

---

### 3. Distance Score (20% Weight) - **THIRD PRIORITY**

**Purpose**: Prefer supervisors who are closer to the student's placement location.

**Formula**: `1 / (1 + distance_in_km)`

**Characteristics**:
- **Closer = Higher Score**
- **Farther = Lower Score**
- **Unknown Distance** → Returns **0.5** (Neutral score)

**Weighted Score**: `Distance Score × 0.2`

**Examples**:
```
Distance: 10 km
→ Distance Score: 1 / (1 + 10) = 0.091
→ Weighted Score: 0.091 × 0.2 = 0.018 (1.8 points)

Distance: 50 km
→ Distance Score: 1 / (1 + 50) = 0.020
→ Weighted Score: 0.020 × 0.2 = 0.004 (0.4 points)

Distance: 200 km
→ Distance Score: 1 / (1 + 200) = 0.005
→ Weighted Score: 0.005 × 0.2 = 0.001 (0.1 points)
```

**Note**: Distance is calculated using the Haversine formula between:
- Supervisor's hometown address (latitude/longitude)
- Student's placement company address (latitude/longitude)

---

### 4. Workload Score (10% Weight) - **LOWEST PRIORITY**

**Purpose**: Distribute workload evenly among supervisors.

**Formula**: `1 - (current_assignments / supervisor_quota)`

**Characteristics**:
- **Less Loaded = Higher Score**
- **More Loaded = Lower Score**
- **Quota = 0** → Returns **0.0**

**Weighted Score**: `Workload Score × 0.1`

**Examples**:
```
Supervisor Quota: 10
Current Assignments: 2
→ Workload Score: 1 - (2/10) = 0.8
→ Weighted Score: 0.8 × 0.1 = 0.08 (8 points)

Supervisor Quota: 10
Current Assignments: 8
→ Workload Score: 1 - (8/10) = 0.2
→ Weighted Score: 0.2 × 0.1 = 0.02 (2 points)

Supervisor Quota: 10
Current Assignments: 10 (Full)
→ Workload Score: 1 - (10/10) = 0.0
→ Weighted Score: 0.0 × 0.1 = 0.0 (0 points)
```

---

## Complete Scoring Example

### Scenario:
- **Student**: CB23112 (Computer Science program)
- **Placement**: Software Development Company, 25 km from supervisor
- **Supervisor A**: 
  - Preferred Coursework: "Computer Science"
  - Travel Preference: "local"
  - Distance: 25 km
  - Quota: 10, Current: 3

### Calculation:

1. **Course Match**:
   - Program: "Computer Science" matches "Computer Science"
   - Raw Score: 1.0
   - Weighted: 1.0 × 0.4 = **0.4**

2. **Preference Match**:
   - Travel Preference: "local"
   - Distance: 25 km (≤ 50 km)
   - Raw Score: 1.0
   - Weighted: 1.0 × 0.3 = **0.3**

3. **Distance Score**:
   - Distance: 25 km
   - Raw Score: 1 / (1 + 25) = 0.038
   - Weighted: 0.038 × 0.2 = **0.008**

4. **Workload Score**:
   - Quota: 10, Current: 3
   - Raw Score: 1 - (3/10) = 0.7
   - Weighted: 0.7 × 0.1 = **0.07**

### **Total Score**: 0.4 + 0.3 + 0.008 + 0.07 = **0.778** (77.8%)

---

## Ranking and Selection

1. **Calculate Score**: All eligible supervisors are scored using the formula above
2. **Sort by Score**: Supervisors are sorted in **descending order** (highest score first)
3. **Top N Selection**: System returns top N supervisors (default: 3-10 depending on context)
4. **Display**: Supervisors are shown with:
   - Total score
   - Score breakdown (for transparency)
   - Distance in kilometers
   - Available quota slots

---

## Special Cases

### Quota Override
- When enabled, supervisors at quota limit are included
- They still receive workload scores (likely 0.0)
- Coordinator must provide override reason

### Missing Data
- **Missing Coordinates**: Distance score defaults to 0.5 (neutral)
- **Missing Preferred Coursework**: Course match defaults to 0.0
- **Missing Travel Preference**: Preference match defaults to 0.5 (neutral)

### Administrative Positions
- Lecturers with administrative positions are **automatically excluded**
- Even if they meet other criteria, they cannot be assigned
- This ensures supervisors can focus on student supervision

---

## Current Scoring Priorities (Summary)

### Priority Order:
1. **Course Match (40%)** - Most important for academic alignment
2. **Preference Match (30%)** - Respects supervisor's travel preferences
3. **Distance (20%)** - Prefers closer supervisors
4. **Workload (10%)** - Distributes assignments evenly

### Key Insights:
- **Course expertise is the most important factor** (40% weight)
- **Travel preference is highly valued** (30% weight)
- **Distance matters but is secondary** (20% weight)
- **Workload balance is considered but least important** (10% weight)

---

## Potential Improvements to Consider

### Current System Strengths:
✅ Prioritizes academic match (course expertise)  
✅ Respects supervisor preferences  
✅ Considers practical factors (distance, workload)  
✅ Transparent scoring breakdown  

### Potential Adjustments:
1. **Increase Distance Weight**: If proximity is more important, increase from 20% to 25-30%
2. **Decrease Course Match Weight**: If flexibility is needed, reduce from 40% to 30-35%
3. **Add Research Group Match**: Match students with supervisors in same research group
4. **Add Department Match Bonus**: Small bonus for same department (even if not required)
5. **Adjust Workload Weight**: Increase if workload balance is critical

### Example Adjusted Formula:
```
Total Score = (Course Match × 35%) + (Preference Match × 30%) + (Distance Score × 25%) + (Workload Score × 10%)
```

---

## Testing the Scoring System

### To Verify Scoring:
1. Navigate to Supervisor Assignment page
2. Click "Assign" for a student
3. View recommended supervisors
4. Check score breakdown in the modal
5. Verify calculations match the formula

### To Test Edge Cases:
- Student with no matching coursework → Course match should be 0.0
- Supervisor with "local" preference but >50 km → Preference match should be 0.0
- Supervisor at full quota → Should not appear (unless override enabled)
- Supervisor with administrative position → Should not appear at all

---

## Conclusion

The current scoring system prioritizes **academic alignment** (course match) while balancing **practical considerations** (preference, distance, workload). The weights can be adjusted based on institutional priorities and feedback from coordinators and supervisors.

**For questions or adjustments, contact the system administrator.**


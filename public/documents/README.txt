STUDENT GUIDANCE DOCUMENTS - INSTRUCTIONS FOR ADMIN
====================================================

CURRENT STATUS:
--------------
The system is configured to serve TWO PDF files:
1. course-verification-guide.pdf
2. placement-application-guide.pdf

Currently, these PDF files do NOT exist - you need to create and upload them.

WHAT'S PROVIDED:
---------------
We've created comprehensive HTML guide files that you can convert to PDF:

1. COURSE VERIFICATION GUIDE
   - Location: public/documents/course-verification-guide.html
   - Purpose: Helps students prepare and submit course verification applications
   - Used on: Course Verification page

2. PLACEMENT APPLICATION GUIDE
   - Location: public/documents/placement-application-guide.html
   - Purpose: Helps students apply for internship placements
   - Used on: Internship Placement Applications page

You can open these files in any web browser to view them before converting.

HOW TO CREATE THE PDF FILES:
----------------------------

Option 1: Convert HTML to PDF (Easiest & Recommended)
For EACH guide file, follow these steps:

1. Open the HTML file in Google Chrome or any modern browser
   - course-verification-guide.html
   - placement-application-guide.html

2. Press Ctrl+P (or Cmd+P on Mac) to print

3. Select "Save as PDF" as the destination

4. Adjust print settings if needed:
   - Remove headers and footers
   - Set margins to "None" or "Minimum"
   - Enable "Background graphics"

5. Click "Save" and name it appropriately:
   - course-verification-guide.pdf
   - placement-application-guide.pdf

6. Save both PDFs in the same folder (public/documents/)

Option 2: Create Your Own PDF
1. Use Microsoft Word, Adobe Acrobat, or any PDF editor
2. Use the content from the HTML files as a reference/template
3. Add your institution's logo and branding
4. Customize colors, fonts, and formatting
5. Save with the correct filenames in public/documents/ folder
6. Keep HTML files for future reference

Option 3: Temporarily Use HTML (Quick Fix for Testing)
If you want to test immediately before creating the PDFs:

For Course Verification:
1. Go to: resources/views/livewire/course-verification-table.blade.php
2. Find line with: asset('documents/course-verification-guide.pdf')
3. Change to: asset('documents/course-verification-guide.html')

For Placement Application:
1. Go to: resources/views/livewire/student-placement-application-table.blade.php
2. Find line with: asset('documents/placement-application-guide.pdf')
3. Change to: asset('documents/placement-application-guide.html')

Later, change them back to .pdf when you have the PDF files ready

IMPORTANT NOTES:
---------------
- The maximum file size students can upload is 10 MB
- Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG
- The guide should be clear, professional, and easy to follow
- Update contact information in the guide to match your institution
- Consider adding your institution's logo to the header

FILE LOCATIONS & USAGE:
-----------------------
1. Course Verification Guide
   Link: public/documents/course-verification-guide.pdf
   Location: Course Verification page
   Button: "Download Guide" (top right of Credit Requirements section)

2. Placement Application Guide
   Link: public/documents/placement-application-guide.pdf
   Location: Internship Placement Applications page
   Button: "Application Guide" (top right of page header)

CUSTOMIZATION CHECKLIST:
------------------------
Before creating your PDFs, customize the HTML files with:

☐ Your institution's name (replace "InternLink" references)
☐ Your institution's logo (replace the "IL" placeholder circle)
☐ Actual support contact information:
   - Email addresses
   - Phone numbers
   - Office hours
   - Office locations
☐ Specific requirements for your program:
   - Credit hour requirements
   - Internship duration requirements
   - Specific document formats
☐ Your institution's branding colors
☐ Any additional guidelines or policies
☐ Course code examples that match your curriculum
☐ State/country lists (if needed for international programs)

TESTING:
--------
After uploading the PDF files:
1. Clear your browser cache
2. Log in as a student
3. Navigate to each page:
   - Course Verification page
   - Internship Placement Applications page
4. Click the download buttons to test
5. Verify PDFs open correctly and are readable

MAINTENANCE:
-----------
- Keep the HTML files as master templates
- Update HTML first, then regenerate PDFs when making changes
- Version control: Update version numbers in the footer
- Review guides annually or when policies change

TROUBLESHOOTING:
---------------
Issue: Download button shows but PDF doesn't download
Solution: Check that PDF files exist in public/documents/ folder
          and have correct filenames (case-sensitive)

Issue: PDFs look different from HTML preview
Solution: Use "Print to PDF" feature in Chrome for best results
          Enable "Background graphics" in print settings

Issue: File size is too large
Solution: Compress PDFs using online tools or Adobe Acrobat
          Remove unnecessary images or optimize them

After creating and testing the PDF files, you can keep or delete this README.txt.


COURSE VERIFICATION GUIDE - INSTRUCTIONS FOR ADMIN
===================================================

CURRENT STATUS:
--------------
The system is configured to serve a PDF file named "course-verification-guide.pdf"
Currently, this file does NOT exist - you need to create and upload it.

WHAT'S PROVIDED:
---------------
We've created a comprehensive HTML guide file:
- Location: public/documents/course-verification-guide.html
- You can open this file in any web browser to view it
- It contains all the essential information students need

HOW TO CREATE THE PDF:
----------------------

Option 1: Convert HTML to PDF (Easiest)
1. Open course-verification-guide.html in Google Chrome or any modern browser
2. Press Ctrl+P (or Cmd+P on Mac) to print
3. Select "Save as PDF" as the destination
4. Click "Save" and name it "course-verification-guide.pdf"
5. Save the PDF in the same folder (public/documents/)

Option 2: Create Your Own PDF
1. Use Microsoft Word, Adobe Acrobat, or any PDF editor
2. Use the content from the HTML file as a reference/template
3. Add your institution's logo and branding
4. Save as "course-verification-guide.pdf" in public/documents/ folder
5. Delete or keep the HTML file for future reference

Option 3: Temporarily Use HTML (Quick Fix)
If you want to test immediately before creating the PDF:
1. Go to: resources/views/livewire/course-verification-table.blade.php
2. Find line with: asset('documents/course-verification-guide.pdf')
3. Change to: asset('documents/course-verification-guide.html')
4. Later, change it back to .pdf when you have the PDF file

IMPORTANT NOTES:
---------------
- The maximum file size students can upload is 10 MB
- Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG
- The guide should be clear, professional, and easy to follow
- Update contact information in the guide to match your institution
- Consider adding your institution's logo to the header

FILE LOCATION:
-------------
The link points to: public/documents/course-verification-guide.pdf
Students will see a "Download Guide" button on the Course Verification page.

CUSTOMIZATION:
-------------
Feel free to customize the HTML template with:
- Your institution's name and logo
- Specific course codes and examples
- Actual support contact information
- Any additional requirements or guidelines
- Your institution's branding colors

After creating the PDF file, you can delete this README.txt if you wish.


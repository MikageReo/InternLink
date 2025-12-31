<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Verification Application Ready for Review</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #dbeafe;
            color: #1e40af;
            border: 2px solid #3b82f6;
        }

        .content {
            margin: 30px 0;
        }

        .application-details {
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: bold;
            color: #374151;
        }

        .detail-value {
            color: #6b7280;
        }

        .remarks-section {
            background-color: #fef3c7;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }

        .remarks-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
        }

        .remarks-content {
            color: #78350f;
            font-style: italic;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #e9ecef;
            margin-top: 30px;
            color: #6b7280;
            font-size: 14px;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 15px 0;
        }

        .button:hover {
            background-color: #2563eb;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">Internlink System</div>
            <h1>Course Verification Application Ready for Review</h1>
        </div>

        <div class="content">
            <p>Dear Coordinator,</p>

            <p>A course verification application has been <span class="status-badge">Approved by Academic Advisor</span> and is now ready for your review.</p>

            <div class="application-details">
                <div class="detail-row">
                    <span class="detail-label">Application ID:</span>
                    <span class="detail-value">#{{ $courseVerification->courseVerificationID }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Student Name:</span>
                    <span class="detail-value">{{ $studentName }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Student ID:</span>
                    <span class="detail-value">{{ $courseVerification->studentID }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Current Credits:</span>
                    <span class="detail-value">{{ $courseVerification->currentCredit }} / 130</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Application Date:</span>
                    <span class="detail-value">{{ $courseVerification->applicationDate->format('F d, Y') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Academic Advisor:</span>
                    <span class="detail-value">{{ $academicAdvisorName }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Approved Date:</span>
                    <span class="detail-value">{{ $courseVerification->updated_at->format('F d, Y \a\t g:i A') }}</span>
                </div>
            </div>

            @if ($courseVerification->remarks)
                <div class="remarks-section">
                    <div class="remarks-title">📝 Academic Advisor's Remarks:</div>
                    <div class="remarks-content">{{ $courseVerification->remarks }}</div>
                </div>
            @endif

            <p>
                <strong>Action Required:</strong><br>
                • Please review this application in the Course Verification Management system<br>
                • Make a final decision to approve or reject the application<br>
                • Provide remarks if rejecting the application
            </p>

            <p style="text-align: center;">
                <a href="{{ url('/lecturer/course-verification-management') }}" class="button">
                    Review Application
                </a>
            </p>
        </div>

        <div class="footer">
            <p>
                This is an automated message from the Internlink System.<br>
                Please log in to review and process this application.
            </p>
        </div>
    </div>
</body>

</html>


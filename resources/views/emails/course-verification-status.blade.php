<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Verification Status Update</title>
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
        }

        .approved {
            background-color: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }

        .rejected {
            background-color: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
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
            <h1>Course Verification Status Update</h1>
        </div>

        <div class="content">
            <p>Dear {{ $studentName }},</p>

            <p>We are writing to inform you that your course verification application has been reviewed and
                <strong>
                    <span class="status-badge {{ $status === 'approved' ? 'approved' : 'rejected' }}">
                        {{ ucfirst($status) }}
                    </span>
                </strong>
            </p>

            @if ($status === 'approved')
                <p style="color: #065f46; font-weight: bold; font-size: 16px;">
                    🎉 Congratulations! Your course verification has been approved. You have successfully met the credit
                    requirements for your program.
                </p>
            @else
                <p style="color: #991b1b; font-weight: bold;">
                    Unfortunately, your course verification application has been rejected. Please review the remarks
                    below and consider resubmitting with the necessary corrections.
                </p>
            @endif

            <div class="application-details">
                <h3 style="margin-top: 0; color: #1f2937;">Application Details</h3>

                <div class="detail-row">
                    <span class="detail-label">Application ID:</span>
                    <span class="detail-value">#{{ $courseVerification->courseVerificationID }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Current Credits:</span>
                    <span class="detail-value">{{ $courseVerification->currentCredit }} / 118</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Application Date:</span>
                    <span class="detail-value">{{ $courseVerification->applicationDate->format('F d, Y') }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Reviewed By:</span>
                    <span class="detail-value">{{ $lecturerName }}</span>
                </div>

                <div class="detail-row">
                    <span class="detail-label">Review Date:</span>
                    <span class="detail-value">{{ $courseVerification->updated_at->format('F d, Y \a\t g:i A') }}</span>
                </div>
            </div>

            @if ($courseVerification->remarks)
                <div class="remarks-section">
                    <div class="remarks-title">📝 Lecturer's Remarks:</div>
                    <div class="remarks-content">{{ $courseVerification->remarks }}</div>
                </div>
            @endif

            @if ($status === 'approved')
                <p>
                    <strong>Next Steps:</strong><br>
                    • Your course verification is now complete<br>
                    • You may proceed with your program requirements<br>
                    • Keep this email for your records<br>
                    • Contact the academic office if you have any questions
                </p>
            @else
                <p>
                    <strong>Next Steps:</strong><br>
                    • Review the lecturer's remarks carefully<br>
                    • Gather any additional required documentation<br>
                    • Submit a new application when ready<br>
                    • Contact your academic advisor if you need guidance
                </p>

                <p style="text-align: center;">
                    <a href="{{ url('/student/course-verification') }}" class="button">
                        Apply Again
                    </a>
                </p>
            @endif
        </div>

        <div class="footer">
            <p>
                This is an automated message from the Internlink System.<br>
                If you have any questions, please contact the academic office.
            </p>
            {{-- <p>
                <strong>Academic Office</strong><br>
                Email: academic@university.edu<br>
                Phone: +1 (555) 123-4567
            </p> --}}
        </div>
    </div>
</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Defer Request Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            margin: 10px 0;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .reason-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
        }
        .remarks-box {
            background-color: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #0066cc;
        }
        .footer {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
        }
        .next-steps {
            background-color: #f0f8ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗓️ Defer Request Status Update</h1>
        <p>Your defer request has been reviewed</p>
        <div class="status-badge status-{{ strtolower($overallStatus) }}">
            {{ $overallStatus }}
        </div>
    </div>

    <div class="content">
        <h2>Dear {{ $student->user->name }},</h2>

        <p>We are writing to inform you about the status update of your defer request.</p>

        <h3>📋 Request Details</h3>
        <div class="info-row">
            <span class="info-label">Request ID:</span>
            <span class="info-value">#{{ $request->deferID }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Student ID:</span>
            <span class="info-value">{{ $student->studentID }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Application Date:</span>
            <span class="info-value">{{ $request->applicationDate->format('F d, Y') }}</span>
        </div>

        <h3>📝 Your Reason</h3>
        <div class="reason-box">
            {{ $request->reason }}
        </div>

        <h3>✅ Approval Status</h3>
        <div class="info-row">
            <span class="info-label">Committee Status:</span>
            <span class="info-value">
                <span class="status-badge status-{{ strtolower($request->committeeStatus) }}">
                    {{ $request->committeeStatus }}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Coordinator Status:</span>
            <span class="info-value">
                <span class="status-badge status-{{ strtolower($request->coordinatorStatus) }}">
                    {{ $request->coordinatorStatus }}
                </span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Overall Status:</span>
            <span class="info-value">
                <span class="status-badge status-{{ strtolower($overallStatus) }}">
                    {{ $overallStatus }}
                </span>
            </span>
        </div>

        @if($request->remarks)
            <h3>💬 Remarks from Reviewers</h3>
            <div class="remarks-box">
                {{ $request->remarks }}
            </div>
        @endif

        <div class="next-steps">
            <h3>📌 Next Steps</h3>
            @if($overallStatus === 'Approved')
                <p><strong>Congratulations!</strong> Your defer request has been approved by both the committee and coordinator.</p>
                <p>You may proceed with your defer arrangements for the next semester.</p>
            @elseif($overallStatus === 'Rejected')
                <p>Unfortunately, your defer request has been rejected.</p>
                <p>Please review the remarks above for the reason(s) for rejection.</p>
                <p>If you have questions or would like to discuss this decision, please contact your academic advisor or the internship coordinator.</p>
                <p>You may submit a new defer request with additional information if circumstances change.</p>
            @else
                <p>Your defer request is currently under review.</p>
                @if($request->committeeStatus === 'Pending')
                    <p>Status: Awaiting committee review</p>
                @elseif($request->coordinatorStatus === 'Pending')
                    <p>Status: Committee approved, awaiting coordinator review</p>
                @endif
                <p>You will receive another notification once the review process is complete.</p>
            @endif
        </div>

        @if($overallStatus !== 'Pending')
            <p>You can view the complete details of your request by logging into the student portal.</p>
        @endif

        <p>If you have any questions regarding this decision, please don't hesitate to contact the academic office.</p>

        <p>Best regards,<br>
        <strong>Academic Office</strong><br>
        Internship Management System</p>
    </div>

    <div class="footer">
        <p>This is an automated message from the Internship Management System.</p>
        <p>Please do not reply to this email. For support, contact the academic office.</p>
        <p>© {{ date('Y') }} Internship Management System. All rights reserved.</p>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Request Status Update</title>
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
            text-align: center;
            margin-bottom: 30px;
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
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .info-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        .next-steps {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2196f3;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔄 Change Request Status Update</h1>
        <p>Your placement change request has been reviewed</p>
    </div>

    <p>Dear {{ $student->user->name }},</p>

    <p>We are writing to inform you about the status of your placement change request.</p>

    <!-- Request Information -->
    <div class="info-section">
        <h3>📋 Request Details</h3>
        <div class="info-row">
            <span class="info-label">Request ID:</span>
            <span>#{{ $request->justificationID }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Request Date:</span>
            <span>{{ $request->requestDate->format('F j, Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Current Company:</span>
            <span>{{ $placementApplication->companyName }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Position:</span>
            <span>{{ $placementApplication->position }}</span>
        </div>
        @if($request->decisionDate)
        <div class="info-row">
            <span class="info-label">Decision Date:</span>
            <span>{{ $request->decisionDate->format('F j, Y') }}</span>
        </div>
        @endif
    </div>

    <!-- Status Information -->
    <div class="info-section">
        <h3>📊 Review Status</h3>
        <div class="info-row">
            <span class="info-label">Overall Status:</span>
            <span class="status-badge status-{{ strtolower($overallStatus) }}">{{ $overallStatus }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Committee Status:</span>
            <span class="status-badge status-{{ strtolower($request->committeeStatus) }}">{{ $request->committeeStatus }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Coordinator Status:</span>
            <span class="status-badge status-{{ strtolower($request->coordinatorStatus) }}">
                {{ $request->coordinatorStatus }}
                @if($request->committeeStatus === 'Rejected' && $request->coordinatorStatus === 'Rejected' && !$request->coordinatorID)
                    (Auto-rejected)
                @endif
            </span>
        </div>
    </div>

    <!-- Reason for Change -->
    <div class="info-section">
        <h3>📝 Your Reason for Change</h3>
        <p style="margin: 0; font-style: italic;">{{ $request->reason }}</p>
    </div>

    <!-- Reviewer Remarks -->
    @if($request->remarks)
    <div class="info-section">
        <h3>💬 Reviewer Remarks</h3>
        <p style="margin: 0;">{{ $request->remarks }}</p>
    </div>
    @endif

    <!-- Status-specific message and next steps -->
    @if($overallStatus === 'Approved')
        <div class="next-steps">
            <h3>🎉 Congratulations! Your change request has been approved.</h3>
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>You can now submit a new placement application through the student portal</li>
                <li>Your current placement application will remain active until you accept a new placement</li>
                <li>Please submit your new application as soon as possible</li>
                <li>Contact your coordinator if you have any questions about the next steps</li>
            </ul>
        </div>
    @elseif($overallStatus === 'Rejected')
        <div class="next-steps">
            <h3>❌ Your change request has been rejected.</h3>
            <p><strong>What this means:</strong></p>
            <ul>
                <li>Your current placement application remains unchanged</li>
                <li>You should continue with your existing internship arrangement</li>
                <li>If you have concerns, please contact your coordinator to discuss alternatives</li>
                <li>You may submit a new change request in the future if circumstances change</li>
            </ul>
        </div>
    @else
        <div class="next-steps">
            <h3>⏳ Your change request is still under review.</h3>
            <p>Please wait for the review process to complete. You will receive another notification once a final decision has been made.</p>
        </div>
    @endif

    <!-- Contact Information -->
    <div class="info-section">
        <h3>📞 Need Help?</h3>
        <p>If you have any questions about this change request or need assistance, please contact:</p>
        <ul>
            <li><strong>Academic Coordinator:</strong> coordinator@university.edu</li>
            <li><strong>Internship Committee:</strong> internship.committee@university.edu</li>
            <li><strong>Student Support:</strong> support@university.edu</li>
        </ul>
    </div>

    <div class="footer">
        <p>This is an automated message from the Internship Management System.</p>
        <p>Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} University Internship Management System</p>
    </div>
</body>
</html>

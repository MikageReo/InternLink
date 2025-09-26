<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Internship Placement Application Status Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .status-approved { color: #28a745; font-weight: bold; }
        .status-rejected { color: #dc3545; font-weight: bold; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .details { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; font-size: 14px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Internship Placement Application Update</h1>
            <p>Hello {{ $student->user->name }},</p>
            <p>Your internship placement application has been updated.</p>
        </div>

        <div class="details">
            <h3>Application Details</h3>
            <p><strong>Application ID:</strong> #{{ $application->applicationID }}</p>
            <p><strong>Company:</strong> {{ $application->companyName }}</p>
            <p><strong>Position:</strong> {{ $application->position }}</p>
            <p><strong>Application Date:</strong> {{ $application->applicationDate->format('F d, Y') }}</p>
        </div>

        <div class="details">
            <h3>Status Information</h3>
            <p><strong>Committee Status:</strong>
                <span class="status-{{ strtolower($application->committeeStatus) }}">
                    {{ $application->committeeStatus }}
                </span>
            </p>
            <p><strong>Coordinator Status:</strong>
                <span class="status-{{ strtolower($application->coordinatorStatus) }}">
                    {{ $application->coordinatorStatus }}
                </span>
            </p>
            <p><strong>Overall Status:</strong>
                <span class="status-{{ strtolower($overallStatus) }}">
                    {{ $overallStatus }}
                </span>
            </p>
        </div>

        @if($application->remarks)
        <div class="details">
            <h3>Remarks</h3>
            <p>{{ $application->remarks }}</p>
        </div>
        @endif

        @if($overallStatus === 'Approved')
        <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h3>🎉 Congratulations!</h3>
            <p>Your internship placement application has been approved by both the committee and coordinator.</p>
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Log in to your student portal to accept or decline this placement</li>
                <li>Review the company details and internship requirements</li>
                <li>Make your decision within the specified timeframe</li>
            </ul>
        </div>
        @elseif($overallStatus === 'Rejected')
        <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h3>Application Status</h3>
            <p>Unfortunately, your internship placement application has been rejected.</p>
            <p>Please review the remarks above and consider submitting a new application with the suggested improvements.</p>
        </div>
        @else
        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <h3>Application Under Review</h3>
            <p>Your application is currently being reviewed. We will notify you once both committee and coordinator reviews are complete.</p>
        </div>
        @endif

        <p style="margin-top: 20px;">
            <a href="{{ route('student.placementApplications') }}"
               style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
                View Application Details
            </a>
        </p>

        <div class="footer">
            <p>Best regards,<br>
            Academic Affairs Office<br>
            {{ config('app.name') }}</p>

            <p><em>This is an automated message. Please do not reply to this email.</em></p>
        </div>
    </div>
</body>
</html>

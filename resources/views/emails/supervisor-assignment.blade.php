<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Assignment Notification</title>
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
            background-color: #7c3aed;
            color: white;
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
            background-color: #d4edda;
            color: #155724;
            margin: 10px 0;
        }
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
        .supervisor-info {
            background-color: #ede9fe;
            padding: 15px;
            border-left: 4px solid #7c3aed;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #7c3aed;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👨‍🏫 Supervisor Assigned</h1>
        <p>You have been assigned a supervisor for your internship placement</p>
    </div>

    <div>
        <p>Dear {{ $student->user->name }},</p>

        <p>We are pleased to inform you that a supervisor has been assigned to oversee your internship placement. Details are provided below:</p>

        <div class="status-badge">✅ Supervisor Assigned</div>

        <div class="supervisor-info">
            <h3 style="margin-top: 0; color: #7c3aed;">Supervisor Information</h3>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span>{{ $supervisor->user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Lecturer ID:</span>
                <span>{{ $supervisor->lecturerID }}</span>
            </div>
            @if($supervisor->position)
            <div class="info-row">
                <span class="info-label">Position:</span>
                <span>{{ $supervisor->position }}</span>
            </div>
            @endif
            @if($supervisor->department)
            <div class="info-row">
                <span class="info-label">Department:</span>
                <span>{{ $supervisor->department }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span><a href="mailto:{{ $supervisor->user->email }}" style="color: #7c3aed;">{{ $supervisor->user->email }}</a></span>
            </div>
            @if($assignment->distance_km)
            <div class="info-row">
                <span class="info-label">Distance:</span>
                <span>{{ number_format($assignment->distance_km, 2) }} km</span>
            </div>
            @endif
        </div>

        @if($placement)
        <div class="info-section">
            <h3 style="margin-top: 0;">Placement Details</h3>
            <div class="info-row">
                <span class="info-label">Company:</span>
                <span>{{ $placement->companyName }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Location:</span>
                <span>{{ $placement->companyFullAddress }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Position:</span>
                <span>{{ $placement->position }}</span>
            </div>
        </div>
        @endif

        <div class="info-section">
            <h3 style="margin-top: 0;">Assignment Details</h3>
            <div class="info-row">
                <span class="info-label">Assigned Date:</span>
                <span>{{ $assignment->assigned_at->format('F d, Y') }}</span>
            </div>
            @if($assignment->assignment_notes)
            <div style="margin-top: 10px;">
                <span class="info-label">Notes:</span>
                <p style="margin-top: 5px;">{{ $assignment->assignment_notes }}</p>
            </div>
            @endif
        </div>

        <div style="margin: 30px 0; padding: 15px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px;">
            <h3 style="margin-top: 0;">Next Steps</h3>
            <ul style="margin: 0; padding-left: 20px;">
                <li>Contact your supervisor to schedule an initial meeting</li>
                <li>Discuss your internship objectives and expectations</li>
                <li>Coordinate evaluation schedules and site visits</li>
                <li>Maintain regular communication throughout your internship</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="{{ route('student.dashboard') }}" class="button">View Dashboard</a>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated notification from the Internlink System.</p>
        <p>If you have any questions, please contact your coordinator.</p>
        <p style="margin-top: 20px;">© {{ date('Y') }} Internlink. All rights reserved.</p>
    </div>
</body>
</html>


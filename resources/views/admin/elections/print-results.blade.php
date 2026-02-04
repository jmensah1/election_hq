<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $election->title }} - Results</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1a202c;
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px;
            background-color: #fff;
        }

        /* Branding Header */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 4px solid #1a202c;
            padding-bottom: 20px;
        }
        
        .brand-logo {
            max-height: 80px;
            max-width: 250px;
        }

        .election-title {
            text-align: right;
        }
        
        .election-title h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 800;
        }
        
        .election-title p {
            margin: 5px 0 0;
            color: #718096;
            font-size: 14px;
        }

        /* Position Block */
        .position-section {
            margin-bottom: 60px;
            page-break-inside: avoid;
        }
        
        .position-title {
            background-color: #1a202c;
            color: white;
            padding: 10px 20px;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        /* Results Grid */
        .results-grid {
            display: flex;
            justify-content: center; /* Center if few candidates, or use space-around */
            gap: 20px;
            flex-wrap: wrap;
            align-items: flex-end; /* Align bottom for bars */
            min-height: 350px; /* Give space for bars */
        }

        .candidate-card {
            flex: 1;
            min-width: 150px;
            max-width: 250px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        /* Photo */
        .candidate-photo-wrapper {
            position: relative;
            margin-bottom: 15px;
            z-index: 2;
        }
        
        .candidate-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background-color: #e2e8f0;
        }

        .winner-star {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #f59e0b; /* Amber/Gold */
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Names & Info */
        .candidate-name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .candidate-votes {
            font-size: 13px;
            color: #718096;
            margin-bottom: 10px;
        }

        /* Bar Graph */
        .bar-container {
            width: 100%;
            height: 200px; /* Max height of bar area */
            display: flex;
            align-items: flex-end;
            justify-content: center;
            position: relative;
        }

        .bar {
            width: 60px;
            min-height: 2px; /* Ensure 0% shows line */
            transition: height 0.5s;
            position: relative;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
            /* Default Color */
            background-color: #4299e1; /* Blue */
        }
        
        /* Winner / Colors */
        .winner .bar {
            background-color: #f56565; /* Red/Distinctive */
        }

        .percentage-label {
            position: absolute;
            top: 30px; /* Inside the bar or just below top */
            left: 0;
            right: 0;
            text-align: center;
            color: white;
            font-weight: 800;
            font-size: 20px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        /* Print Specifics */
        @media print {
            body {
                padding: 0;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .position-section {
                break-inside: avoid;
            }
            .bar, .position-title, .winner-star {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Color Palette for different candidates if not winner-based could utilize nth-child */
        .candidate-card:nth-child(odd) .bar:not(.winner .bar) {
             background-color: #4299e1;
        }
        .candidate-card:nth-child(even) .bar:not(.winner .bar) {
             background-color: #2b6cb0;
        }

    </style>
</head>
<body onload="window.print()">

    <div class="brand-header">
        <div class="logo-container">
            @if($election->organization && $election->organization->logo_path)
                <img src="{{ asset('storage/' . $election->organization->logo_path) }}" alt="Logo" class="brand-logo">
            @else
                <h2 style="margin:0;">{{ $election->organization->name ?? 'Elections HQ' }}</h2>
            @endif
        </div>
        <div class="election-title">
            <h1>{{ $election->title }}</h1>
            <p>Official Results • Generated {{ now()->format('M d, Y') }}</p>
        </div>
    </div>

    @foreach($results as $position)
        <div class="position-section">
            <div class="position-title">
                <span>{{ $position['name'] }}</span>
                <span style="font-size: 0.8em; opacity: 0.8;">{{ number_format($position['totalVotes']) }} Votes Cast</span>
            </div>
            
            <div class="results-grid">
                @foreach($position['candidates'] as $candidate)
                    <div class="candidate-card {{ $candidate['isWinner'] ? 'winner' : '' }}">
                        
                        {{-- Photo --}}
                        <div class="candidate-photo-wrapper">
                            @if($candidate['photo'])
                                <img src="{{ asset('storage/' . $candidate['photo']) }}" alt="{{ $candidate['name'] }}" class="candidate-photo">
                            @else
                                <div class="candidate-photo" style="display:flex;align-items:center;justify-content:center;background:#cbd5e0;color:#fff;">
                                    <span style="font-size:30px;">?</span>
                                </div>
                            @endif
                            
                            @if($candidate['isWinner'])
                                <div class="winner-star">★</div>
                            @endif
                        </div>

                        {{-- Name --}}
                        <div class="candidate-name">{{ $candidate['name'] }}</div>
                        <div class="candidate-votes">{{ number_format($candidate['votes']) }} votes</div>

                        {{-- Bar Graph --}}
                        <div class="bar-container">
                            <div class="bar" style="height: {{ max(5, $candidate['percentage']) }}%;">
                                <div class="percentage-label">{{ $candidate['percentage'] }}%</div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div style="text-align: center; margin-top: 50px; color: #a0aec0; font-size: 12px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
        Powered by Elections HQ
    </div>

</body>
</html>

<?php
/**
 * MedEx Help Landing (no OpenEMR bootstrap dependency).
 *
 * Keep this page self-contained so Module Manager help links never fail
 * with auth/bootstrap side effects.
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>MedEx Module Help</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }
        .wrap {
            max-width: 920px;
            margin: 24px auto;
            background: #fff;
            border: 1px solid #dbe5f1;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }
        .head {
            padding: 20px 24px;
            background: linear-gradient(135deg, #0f4b8f, #0f766e);
            color: #fff;
        }
        .head h1 {
            margin: 0 0 6px;
            font-size: 24px;
        }
        .head p {
            margin: 0;
            opacity: 0.92;
        }
        .body {
            padding: 22px 24px 24px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 12px;
            margin: 16px 0 20px;
        }
        .card {
            border: 1px solid #dbe5f1;
            border-radius: 8px;
            padding: 12px 14px;
            background: #f8fbff;
        }
        .card h3 {
            margin: 0 0 8px;
            font-size: 15px;
            color: #0f4b8f;
        }
        .card p {
            margin: 0;
            font-size: 13px;
            color: #475569;
        }
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        .btn {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: 1px solid transparent;
        }
        .btn-primary {
            background: #0f4b8f;
            color: #fff;
        }
        .btn-secondary {
            background: #fff;
            color: #0f4b8f;
            border-color: #9db8d8;
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <h1>MedEx Module Help</h1>
        <p>Install, setup, and day-to-day usage links.</p>
    </div>
    <div class="body">
        <div class="grid">
            <div class="card">
                <h3>Install + Enable</h3>
                <p>Install the module from Module Manager, then enable it to activate MedEx features.</p>
            </div>
            <div class="card">
                <h3>Initial Setup</h3>
                <p>Use onboarding/splash to connect your practice and validate callback readiness.</p>
            </div>
            <div class="card">
                <h3>User Guide</h3>
                <p>Open the full in-module guide for workflows, credits, reminders, and support details.</p>
            </div>
        </div>

        <div class="actions">
            <a class="btn btn-primary" href="admin/splash.php?minimal=1&site=default">Open Setup</a>
            <a class="btn btn-secondary" href="public/help.php?site=default">Open Full Help</a>
            <a class="btn btn-secondary" href="public/status.php?site=default">Open Status</a>
        </div>
    </div>
</div>
</body>
</html>

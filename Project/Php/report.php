<!DOCTYPE html>
<html>
<head>
    <title>Automobiles Solution</title>
    <link rel="stylesheet" href="../css/report.css">
</head>
<body>
    <h2 class="servh">Service Report</h2>
    <div class="report-form" id="report">
        <form method="post" action="process_report.php">
            <fieldset>
                <legend>Service Report Submission</legend>
                
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br><br>
                
                <label for="service_date">Service Date:</label>
                <input type="date" id="service_date" name="service_date" required><br><br>
                
                <label for="service_type">Service Type:</label>
                <select id="service_type" name="service_type" required>
                    <option value="">Select a service</option>
                    <option value="regular">Regular</option>
                    <option value="home">Home</option>
                    <option value="emergency">Emergency</option>
                </select><br><br>
                
                <label for="feedback">Feedback:</label><br>
                <textarea id="feedback" name="feedback" rows="4" cols="50" required></textarea><br><br>
                
                <input type="submit" value="Submit Report" class="submit-btn">
            </fieldset>
        </form>
    </div>
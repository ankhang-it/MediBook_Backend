# Fix .env file - remove duplicates and set correct URLs
$envFile = "backend\.env"

if (Test-Path $envFile) {
    Write-Host "Fixing .env file..." -ForegroundColor Yellow
    
    # Read all lines
    $lines = Get-Content $envFile
    
    # Remove duplicate PAYOS entries (keep only the last ones)
    $filteredLines = @()
    $payosKeys = @("PAYOS_RETURN_URL", "PAYOS_CANCEL_URL", "PAYOS_WEBHOOK_URL")
    $payosEntries = @{}
    
    foreach ($line in $lines) {
        $trimmedLine = $line.Trim()
        if ($trimmedLine -match "^PAYOS_") {
            $key = $trimmedLine.Split('=')[0]
            $value = $trimmedLine.Split('=')[1]
            $payosEntries[$key] = $value
        } else {
            $filteredLines += $line
        }
    }
    
    # Add the correct PAYOS entries
    $payosEntries["PAYOS_RETURN_URL"] = "http://localhost:3000/"
    $payosEntries["PAYOS_CANCEL_URL"] = "http://localhost:3000/"
    $payosEntries["PAYOS_WEBHOOK_URL"] = "http://localhost:8000/api/payment/callback"
    
    # Add PAYOS entries to filtered lines
    foreach ($key in $payosEntries.Keys) {
        $filteredLines += "$key=$($payosEntries[$key])"
        Write-Host "Set: $key=$($payosEntries[$key])" -ForegroundColor Green
    }
    
    # Write back to file
    $filteredLines | Set-Content $envFile
    
    Write-Host "`n.env file fixed successfully!" -ForegroundColor Green
    Write-Host "Return URL: http://localhost:3000/" -ForegroundColor Cyan
    Write-Host "Cancel URL: http://localhost:3000/" -ForegroundColor Cyan
    Write-Host "Webhook URL: http://localhost:8000/api/payment/callback" -ForegroundColor Cyan
    
} else {
    Write-Error "Error: .env file not found at $envFile"
}

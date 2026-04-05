# Download and run ConfigureRemotingForAnsible.ps1

$url = "https://raw.githubusercontent.com/ansible/ansible-documentation/devel/examples/scripts/ConfigureRemotingForAnsible.ps1"
$output = "$env:TEMP\ConfigureRemotingForAnsible.ps1"

# Download
Invoke-WebRequest -Uri $url -OutFile $output

# Run with the execution policy temporarily changed
powershell -ExecutionPolicy Bypass -File $output

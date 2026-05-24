$base = "http://127.0.0.1:8000/api"
$email = "jonathan+$(Get-Random -Minimum 1000 -Maximum 9999)@test.com"
$password = "12345678"

function Parse-Json($content) {
  try { return $content | ConvertFrom-Json } catch { return $null }
}

function Call($label, $method, $url, $headers, $bodyObj=$null) {
  $body = $null
  if ($null -ne $bodyObj) { $body = ($bodyObj | ConvertTo-Json -Compress) }
  $resp = Invoke-WebRequest -Method $method -Uri $url -Headers $headers -ContentType 'application/json' -Body $body -SkipHttpErrorCheck
  $json = Parse-Json $resp.Content
  [pscustomobject]@{ Label=$label; Status=$resp.StatusCode; Json=$json; Raw=$resp.Content }
}

$headersBase = @{ Accept = 'application/json' }

# 1) Register
$register = Call "register" "POST" ("$base/auth/register") $headersBase @{ name='Jonathan'; email=$email; password=$password }
$tokenReceived = ($register.Json -ne $null -and [string]::IsNullOrWhiteSpace($register.Json.token) -eq $false)
$userId = $register.Json.user.id

# 2) Login
$login = Call "login" "POST" ("$base/auth/login") $headersBase @{ email=$email; password=$password }
$token = $login.Json.token
$tokenOk = ([string]::IsNullOrWhiteSpace($token) -eq $false)

$headersAuth = @{ Accept='application/json'; Authorization=("Bearer $token") }

# 3) GET /me
$me = Call "me" "GET" ("$base/me") $headersAuth

# 4) POST /businesses
$createBiz = Call "create_business" "POST" ("$base/businesses") $headersAuth @{ name='Mi Barbería'; timezone='America/Bogota' }
$bizId = $createBiz.Json.id
if ($null -eq $bizId -and $null -ne $createBiz.Json.data) { $bizId = $createBiz.Json.data.id }

# 5) GET /businesses
$listBiz = Call "list_businesses" "GET" ("$base/businesses") $headersAuth
$bizCount = 0
if ($listBiz.Json -ne $null) { 
    if ($listBiz.Json.data -ne $null) { $bizCount = @($listBiz.Json.data).Count }
    elseif ($listBiz.Json -is [array]) { $bizCount = @($listBiz.Json).Count }
}

# 6) GET /user (ruta default)
$userRoute = Call "user_route" "GET" ("$base/user") $headersAuth

# 7) Logout
$logout = Call "logout" "POST" ("$base/auth/logout") $headersAuth

# 8) Verify token invalid after logout
$meAfter = Call "me_after_logout" "GET" ("$base/me") $headersAuth

# Print summary WITHOUT token
"REGISTER status=$($register.Status) token_received=$tokenReceived user_id=$userId email=$email"
"LOGIN status=$($login.Status) token_received=$tokenOk"
"ME status=$($me.Status) has_user=$([bool]($me.Json.user))"
"CREATE_BUSINESS status=$($createBiz.Status) business_id=$bizId"
"LIST_BUSINESSES status=$($listBiz.Status) count=$bizCount"
"USER_ROUTE status=$($userRoute.Status) has_user=$([bool]($userRoute.Json.id))"
"LOGOUT status=$($logout.Status) ok=$([bool]($logout.Json.ok))"
"ME_AFTER_LOGOUT status=$($meAfter.Status) message=$($meAfter.Json.message)"

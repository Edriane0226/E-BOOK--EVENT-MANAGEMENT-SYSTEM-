<?php
include '../includes/config.php';

// Authenticate user using JWT
$user_data = authenticateUser();
if (!$user_data) {
    header("Location: login.php");
    exit();
}

$user_id = $user_data['id'];

// Fetch user info
$stmt = $conn->prepare("SELECT first_name, last_name, age, birthday, address, contact_number, company_name, email, email_verified, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { 
            background: #141414; 
            color: #fff; 
        }
        .settings-container {
            max-width: 600px;
            margin: 40px auto;
            background: #181818;
            padding: 40px 32px 32px 32px;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(229, 9, 20, 0.15);
        }
        .form-label { 
            color: #e50914; 
            font-weight: 500; 
        }
        .btn-custom {
            background: linear-gradient(45deg, #e50914, #b0060f);
            color: white;
            border: none;
            font-weight: 600;
        }
        .profile-pic-preview {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #e50914;
            margin-bottom: 10px;
        }
        .verify-btn {
            background: #e50914;
            color: #fff;
            font-weight: 600;
            border: none;
        }
        .verified-badge {
            color: #46d369;
            font-size: 1.1em;
            margin-left: 8px;
        }
        .unverified-badge {
            color: #e50914;
            font-size: 1.1em;
            margin-left: 8px;
        }
        .updated-info-container {
            max-width: 600px;
            margin: 40px auto;
            background: #181818;
            padding: 40px 32px 32px 32px;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(229, 9, 20, 0.15);
        }
        .updated-info-title {
            color: #e50914;
            font-weight: 600;
            margin-bottom: 18px;
        }
        .info-label {
            color: #e50914;
            font-weight: 500;
        }
        .input-group-text {
            background: #232323;
            color: #fff;
            border: 1px solid #333;
        }
    </style>
</head>
<body>
<div class="row" style="min-height: 100vh; align-items: center;">
    <div class="col-lg-6 col-12 d-flex align-items-stretch">
        <div class="settings-container flex-grow-1 d-flex flex-column justify-content-center h-100">
            <h3 class="mb-4" style="color:#e50914; font-weight:700;">User Settings</h3>
            <form method="POST" action="update_settings.php" enctype="multipart/form-data" id="settingsForm">
                <div class="text-center mb-4">
                    <img src="<?php echo !empty($user['profile_pic']) ? '../uploads/' . $user['profile_pic'] : '../assets/default-profile.png'; ?>" class="profile-pic-preview" id="profilePicPreview" />
                    <input type="file" class="form-control mt-2" name="profile_pic" accept="image/*" style="max-width:220px;margin:auto;" onchange="previewProfilePic(event)" />
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">First Name</label>
                        <input type="text" class="form-control" name="first_name" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Last Name</label>
                        <input type="text" class="form-control" name="last_name" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Age</label>
                    <input type="number" class="form-control" name="age" min="1" max="120" value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Birthday</label>
                    <input type="date" class="form-control" name="birthday" value="<?php echo isset($user['birthday']) ? htmlspecialchars($user['birthday']) : ''; ?>" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <input type="text" class="form-control" name="contact_number" value="<?php echo isset($user['contact_number']) ? htmlspecialchars($user['contact_number']) : ''; ?>" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Company Name (if applicable)</label>
                    <input type="text" class="form-control" name="company_name" value="<?php echo isset($user['company_name']) ? htmlspecialchars($user['company_name']) : ''; ?>" />
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <input type="email" class="form-control" name="email" id="emailInput" value="<?php echo htmlspecialchars($user['email']); ?>" required />
                        <button type="button" class="btn verify-btn" id="verifyEmailBtn">Verify Email</button>
                        <?php if (!empty($user['email_verified'])): ?>
                            <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified</span>
                        <?php else: ?>
                            <span class="unverified-badge"><i class="bi bi-exclamation-circle-fill"></i> Not Verified</span>
                        <?php endif; ?>
                    </div>
                    <div id="emailVerifyMsg" class="form-text text-success" style="display:none;"></div>
                </div>
                <div class="mb-3" id="verifyCodeBox" style="display:none;">
                    <label class="form-label">Enter Verification Code</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="verificationCodeInput" placeholder="Enter code from email">
                        <button type="button" class="btn btn-custom" id="submitVerifyCodeBtn">Verify</button>
                    </div>
                    <div id="verifyCodeMsg" class="form-text" style="display:none;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Change Password</label>
                    <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password" />
                </div>
                <button type="submit" class="btn btn-custom w-100">Save Changes</button>
            </form>
        </div>
    </div>
    <div class="col-lg-6 col-12 d-flex align-items-stretch">
        <div class="updated-info-container flex-grow-1 d-flex flex-column justify-content-center h-100 w-100" id="updatedInfoContainer">
            <div class="updated-info-title">Updated Information</div>
            <div id="updatedInfoContent">
                <div><span class='info-label'>First Name:</span> <?php echo htmlspecialchars($user['first_name']); ?></div>
                <div><span class='info-label'>Last Name:</span> <?php echo htmlspecialchars($user['last_name']); ?></div>
                <div><span class='info-label'>Age:</span> <?php echo htmlspecialchars($user['age']); ?></div>
                <div><span class='info-label'>Address:</span> <?php echo htmlspecialchars($user['address']); ?></div>
                <div><span class='info-label'>Email:</span> <?php echo htmlspecialchars($user['email']); ?>
                    <?php if (!empty($user['email_verified'])): ?>
                        <span class="verified-badge"><i class="bi bi-patch-check-fill"></i> Verified</span>
                    <?php else: ?>
                        <span class="unverified-badge"><i class="bi bi-exclamation-circle-fill"></i> Not Verified</span>
                    <?php endif; ?>
                </div>
                <div><span class='info-label'>Birthday:</span> <?php echo htmlspecialchars($user['birthday']); ?></div>
                <div><span class='info-label'>Contact Number:</span> <?php echo htmlspecialchars($user['contact_number']); ?></div>
                <div><span class='info-label'>Company Name:</span> <?php echo htmlspecialchars($user['company_name']); ?></div>
            </div>
        </div>
    </div>
</div>
<script>
function previewProfilePic(event) {
    const reader = new FileReader();
    reader.onload = function(){
        document.getElementById('profilePicPreview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

document.getElementById('verifyEmailBtn').addEventListener('click', function() {
    const email = document.getElementById('emailInput').value;
    fetch('../process/send_verification_email.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('emailVerifyMsg').style.display = 'block';
        document.getElementById('emailVerifyMsg').textContent = data.message || 'Verification email sent!';
        if (data.success) {
            document.getElementById('verifyCodeBox').style.display = 'block';
        }
    });
});

document.getElementById('settingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('../process/update_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Show updated info in the new container (now beside the form)
            let html = '';
            html += `<div><span class='info-label'>First Name:</span> ${data.info.first_name}</div>`;
            html += `<div><span class='info-label'>Last Name:</span> ${data.info.last_name}</div>`;
            html += `<div><span class='info-label'>Age:</span> ${data.info.age}</div>`;
            html += `<div><span class='info-label'>Address:</span> ${data.info.address}</div>`;
            html += `<div><span class='info-label'>Email:</span> ${data.info.email} ${(data.info.email_verified ? '<span class=\'verified-badge\'><i class=\'bi bi-patch-check-fill\'></i> Verified</span>' : '<span class=\'unverified-badge\'><i class=\'bi bi-exclamation-circle-fill\'></i> Not Verified</span>')}</div>`;
            html += `<div><span class='info-label'>Birthday:</span> ${data.info.birthday}</div>`;
            html += `<div><span class='info-label'>Contact Number:</span> ${data.info.contact_number}</div>`;
            html += `<div><span class='info-label'>Company Name:</span> ${data.info.company_name}</div>`;
            document.getElementById('updatedInfoContent').innerHTML = html;
            document.getElementById('updatedInfoContainer').style.display = 'block';
        }
    });
});

document.getElementById('submitVerifyCodeBtn').addEventListener('click', function() {
    const code = document.getElementById('verificationCodeInput').value;
    fetch('../process/verify_email_code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'code=' + encodeURIComponent(code)
    })
    .then(res => res.json())
    .then(data => {
        var msg = document.getElementById('verifyCodeMsg');
        msg.style.display = 'block';
        msg.textContent = data.message;
        msg.className = 'form-text ' + (data.success ? 'text-success' : 'text-danger');
        if (data.success) {
            var verifiedBadge = document.querySelector('.verified-badge');
            var unverifiedBadge = document.querySelector('.unverified-badge');
            if (verifiedBadge) verifiedBadge.classList.remove('d-none');
            if (unverifiedBadge) unverifiedBadge.classList.add('d-none');
            setTimeout(function() { document.getElementById('verifyCodeBox').style.display = 'none'; }, 2000);
        }
    });
});
</script>
</body>
</html>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion | Gestion des Absences</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --blue:#075ce8; --deep:#061a3c; --ink:#071b48; --muted:#607093; --line:#dce4f1; }
        * { box-sizing:border-box; }
        body { min-height:100vh; margin:0; color:var(--ink); background:#fff; font-family:Manrope,Arial,sans-serif; }
        .login-page { display:grid; grid-template-columns:minmax(480px, 59.6%) 1fr; min-height:100vh; }
        .showcase { position:relative; overflow:hidden; min-height:100vh; padding:36px 6.8% 30px; color:#fff; background:linear-gradient(105deg,rgba(3,18,45,.96),rgba(5,35,75,.78)),url('https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1800&q=85') center/cover; }
        .showcase::after { content:""; position:absolute; inset:0; pointer-events:none; background:linear-gradient(180deg,rgba(3,13,31,.08),rgba(3,13,31,.67)); }
        .brand, .hero, .metrics, .human-note { position:relative; z-index:1; }
        .brand { display:flex; align-items:center; gap:20px; }
        .mark { display:grid; place-items:center; width:82px; height:82px; border-radius:16px; background:linear-gradient(135deg,#075be7 15%,#00c8c1); box-shadow:0 14px 28px rgba(0,76,208,.43); font-size:31px; font-weight:800; letter-spacing:-5px; }
        .brand-title { margin:0; font-size:23px; font-weight:800; letter-spacing:-1px; }
        .brand-copy { margin:4px 0 0; color:#dce8fb; font-size:15px; }
        .brand-copy span { color:#4bc9ff; }
        .hero { max-width:610px; margin-top:76px; }
        .accent { width:57px; height:2px; margin-bottom:35px; background:#18b9f7; }
        h1 { max-width:560px; margin:0; font-size:46px; line-height:1.18; letter-spacing:-2.5px; font-weight:800; }
        h1 strong { color:#00aef1; font-weight:800; }
        .hero-text { max-width:510px; margin:22px 0 0; color:#d7e2f6; font-size:18px; line-height:1.7; }
        .benefits { display:flex; gap:34px; margin-top:48px; }
        .benefit { width:145px; color:#e7effe; font-size:14px; line-height:1.5; }
        .benefit-icon { display:grid; place-items:center; width:60px; height:60px; margin-bottom:12px; border:1px solid #038fda; border-radius:13px; background:rgba(3,30,71,.62); box-shadow:inset 0 0 22px rgba(18,173,241,.14); font-size:27px; }
        .benefit b { display:block; color:#fff; font-size:16px; }
        .metrics { display:flex; gap:48px; margin-top:clamp(70px,13vh,180px); }
        .metric { display:grid; grid-template-columns:45px auto; align-items:center; column-gap:14px; }
        .metric-icon { display:grid; place-items:center; width:42px; height:42px; border-radius:10px; background:rgba(25,87,162,.52); font-size:20px; }
        .metric b { display:block; font-size:22px; }
        .metric span { color:#d6e1f2; font-size:13px; }
        .human-note { max-width:670px; margin-top:50px; padding-top:13px; border-top:1px solid rgba(202,220,242,.45); color:#dce7f7; font-size:13px; }
        .login-panel { position:relative; display:grid; place-items:center; overflow:hidden; padding:34px; background:radial-gradient(circle at 100% 0,rgba(186,216,255,.7),transparent 21%),#fff; }
        .login-panel::before, .login-panel::after { content:""; position:absolute; z-index:0; border-radius:100px; transform:rotate(45deg); background:linear-gradient(135deg,#dceaff,#f5f9ff); }
        .login-panel::before { width:330px; height:100px; bottom:-28px; left:-83px; }
        .login-panel::after { width:250px; height:82px; bottom:16px; left:2px; opacity:.86; }
        .locale { position:absolute; z-index:2; top:26px; right:8.5%; padding:10px 13px; border:1px solid #e2e8f2; border-radius:13px; background:#f4f7fb; color:#11295c; font-size:14px; font-weight:700; }
        .login-card { position:relative; z-index:1; width:min(100%,510px); text-align:center; }
        .login-card .mark { width:96px; height:96px; margin:0 auto 20px; border-radius:17px; font-size:36px; }
        .login-card h2 { margin:0; font-size:31px; letter-spacing:-1.3px; }
        .login-card h2 span { display:block; margin-top:3px; font-size:23px; font-weight:600; }
        .intro { margin:20px auto 39px; color:var(--muted); font-size:15px; }
        .alert { margin-bottom:20px; padding:13px 16px; border:1px solid #f5b6b6; border-radius:12px; background:#fff4f4; color:#a61b1b; font-size:14px; text-align:left; }
        .field { position:relative; margin-bottom:17px; }
        .field-icon { position:absolute; top:50%; left:20px; transform:translateY(-50%); color:#18366d; font-size:20px; pointer-events:none; }
        .pin-input, .action-select { width:100%; height:64px; border:1px solid var(--line); border-radius:14px; outline:0; background:#f8faff; color:#122b5c; font:600 15px Manrope,Arial,sans-serif; transition:.2s; }
        .pin-input { padding:0 24px 0 58px; letter-spacing:7px; text-align:left; font-size:21px; }
        .action-select { padding:0 20px 0 57px; appearance:none; }
        .select-arrow { position:absolute; top:50%; right:20px; transform:translateY(-50%); color:#18366d; pointer-events:none; }
        .pin-input:focus, .action-select:focus { border-color:#2876ec; background:#fff; box-shadow:0 0 0 4px rgba(7,92,232,.1); }
        .invalid-feedback { margin-top:7px; color:#c52222; font-size:13px; text-align:left; }
        .form-row { display:flex; align-items:center; justify-content:space-between; margin:20px 0 25px; color:#516489; font-size:14px; text-align:left; }
        .form-row label { display:flex; align-items:center; gap:9px; cursor:pointer; }
        .form-row input { width:20px; height:20px; margin:0; accent-color:var(--blue); }
        .admin-note { color:var(--blue); font-weight:700; text-decoration:none; }
        .login-button { display:flex; align-items:center; justify-content:center; gap:22px; width:100%; height:63px; border:0; border-radius:13px; background:linear-gradient(100deg,#0782ef,#0647de); box-shadow:0 14px 25px rgba(5,80,223,.24); color:#fff; font:800 16px Manrope,Arial,sans-serif; cursor:pointer; }
        .login-button:hover { filter:brightness(1.05); transform:translateY(-1px); }
        .terminal { display:flex; align-items:center; gap:15px; margin-top:31px; padding:18px 20px; border:1px solid #dce4f0; border-radius:14px; background:#f1f5fb; text-align:left; }
        .qr { display:grid; place-items:center; min-width:39px; height:39px; color:#0958df; font-size:31px; }
        .terminal b { display:block; font-size:14px; }
        .terminal span { display:block; margin-top:4px; color:#5b6e92; font-size:11px; }
        .secure { position:absolute; z-index:1; right:8.5%; bottom:34px; color:#516489; font-size:12px; }
        @media (max-width:1000px) { .login-page { grid-template-columns:45% 55%; } .showcase { padding:30px 8%; } h1 { font-size:36px; } .benefits { gap:14px; } .metrics { gap:18px; } }
        @media (max-width:760px) { .login-page { display:block; } .showcase { display:none; } .login-panel { min-height:100vh; padding:94px 22px 86px; } .locale { right:22px; } .login-card h2 { font-size:27px; } .intro { margin-bottom:30px; } .secure { right:22px; bottom:24px; } }
        @media (max-width:390px) { .login-panel { padding-inline:16px; } .terminal { padding:15px; } }
    </style>
</head>
<body>
    @php
        $uiMessage = function (?string $message): string {
            return ['Invalid PIN code.' => 'Code PIN invalide.', 'This employee account is inactive.' => 'Ce compte employé est inactif.', 'The pin code field is required.' => 'Le code PIN est obligatoire.', 'The selected action is invalid.' => 'L’action sélectionnée est invalide.'][$message ?? ''] ?? (string) $message;
        };
    @endphp
    <main class="login-page">
        <section class="showcase" aria-label="Présentation Gestion des Absences">
            <div class="brand"><div class="mark">GA</div><div><p class="brand-title">Gestion des Absences</p><p class="brand-copy">Temps <span>•</span> Présence <span>•</span> Sérénité</p></div></div>
            <div class="hero"><div class="accent"></div><h1>Une gestion moderne<br>de la <strong>présence</strong></h1><p class="hero-text">Simplifiez le suivi des présences de vos équipes avec une solution rapide, fiable et sécurisée.</p><div class="benefits"><div class="benefit"><div class="benefit-icon">ϟ</div><b>Pointage rapide</b>par code PIN</div><div class="benefit"><div class="benefit-icon">♢</div><b>Sécurisé</b>et fiable</div><div class="benefit"><div class="benefit-icon">▥</div><b>Rapports clairs</b>en temps réel</div></div></div>
            <div class="metrics"><div class="metric"><div class="metric-icon">♟</div><div><b>1,000+</b><span>Employés suivis</span></div></div><div class="metric"><div class="metric-icon">⌂</div><div><b>+50</b><span>Entreprises</span></div></div><div class="metric"><div class="metric-icon">▥</div><div><b>99.9%</b><span>Disponibilité</span></div></div></div>
            <p class="human-note">La technologie au service de l’humain.</p>
        </section>
        <section class="login-panel">
            <div class="locale">◎&nbsp; FR⌄</div>
            <div class="login-card">
                <div class="mark">GA</div>
                <h2>Gestion des Absences<span>Espace administration</span></h2>
                <p class="intro">Connectez-vous pour accéder à votre espace de travail</p>
                @if (session('error'))<div class="alert">{{ $uiMessage(session('error')) }}</div>@endif
                <form method="POST" action="{{ route('pin.store') }}" autocomplete="off">
                    @csrf
                    <div class="field"><span class="field-icon">⌁</span><input id="pin_code" class="pin-input @error('pin_code') is-invalid @enderror" name="pin_code" type="password" inputmode="numeric" placeholder="Code d’accès" autofocus required>@error('pin_code')<div class="invalid-feedback">{{ $uiMessage($message) }}</div>@enderror</div>
                    <div class="field"><span class="field-icon">◷</span><select id="action" class="action-select" name="action"><option value="check_in">Pointer une entrée</option><option value="check_out">Pointer une sortie</option></select><span class="select-arrow">⌄</span></div>
                    <div class="form-row"><label><input type="checkbox" checked> Se souvenir de moi</label><a class="admin-note" href="#pin_code">Code administrateur</a></div>
                    <button class="login-button" type="submit">Se connecter <span aria-hidden="true">→</span></button>
                </form>
                <div class="terminal"><div class="qr">▦</div><div><b>Accès terminal employé</b><span>Les employés pointent avec leur code PIN.</span></div></div>
            </div>
            <div class="secure">♢ &nbsp; Accès sécurisé • Données protégées</div>
        </section>
    </main>
</body>
</html>

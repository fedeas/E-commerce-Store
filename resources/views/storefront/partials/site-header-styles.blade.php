        .pulse {
            width:7px;
            height:7px;
            border-radius:50%;
            background:var(--green);
            box-shadow:0 0 8px var(--green);
        }

        .site-header {
            position:sticky;
            top:0;
            z-index:90;
            background:rgba(255,255,255,.96);
            border-bottom:1px solid var(--line);
            box-shadow:0 6px 22px rgba(15,23,42,.04);
            backdrop-filter:saturate(130%) blur(10px);
        }

        .header-main {
            height:70px;
            display:grid;
            grid-template-columns:152px 215px minmax(260px, 1fr) auto;
            align-items:center;
            gap:14px;
        }

        .products-trigger {
            height:44px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:10px;
            border:0;
            border-radius:var(--radius);
            background:#18212c;
            color:#fff;
            font-size:13px;
            font-weight:900;
            text-transform:uppercase;
            box-shadow:none;
        }

        .hamb { width:18px; display:grid; gap:4px; }
        .hamb span { display:block; height:2px; border-radius:10px; background:currentColor; }

        .site-header .brand,
        .catalog-overlay .brand {
            min-width:0;
            display:flex;
            align-items:center;
            justify-content: space-around;
            gap:10px;
        }

        .sr-only {
            position:absolute;
            width:1px;
            height:1px;
            padding:0;
            margin:-1px;
            overflow:hidden;
            clip:rect(0, 0, 0, 0);
            white-space:nowrap;
            border:0;
        }

        .site-header .brand-logo,
        .catalog-overlay .brand-logo {
            width:190px;
            max-width:100%;
            height:50px;
            display:flex;
            align-items:center;
        }

        .catalog-overlay .brand-logo {
            width:210px;
            height:58px;
        }

        .site-header .brand-logo img,
        .catalog-overlay .brand-logo img {
            width:100%;
            height:100%;
            object-fit:contain;
            object-position:left center;
        }

        .search-box {
            height:44px;
            display:grid;
            grid-template-columns:minmax(0, 1fr) 48px;
            border:2px solid #d5dce3;
            border-radius:var(--radius);
            overflow:hidden;
            background:#fff;
        }

        .search-box input,
        .search-box button {
            min-width:0;
            border:0;
            outline:0;
            background:#fff;
        }

        .search-box input {
            padding:0 15px;
            color:#222;
            font-size:13px;
            font-weight:600;
        }

        .search-box button {
            background:#00a6a6;
            color:#fff;
            font-weight:900;
        }

        .site-header .header-actions {
            display:flex;
            align-items:center;
            gap:8px;
        }

        .call-wrap,
        .account-wrap { position:relative; }

        .icon-btn {
            height:42px;
            min-width:42px;
            padding:0 12px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            color:#2f3943;
            font-size:12px;
            font-weight:900;
            box-shadow:none;
        }

        .icon-only { width:42px; padding:0; font-size:18px; }
        .cart-btn { background:var(--dark); color:#fff; border-color:var(--dark); }
        .seg-control {
            height:42px;
            display:inline-flex;
            align-items:center;
            overflow:hidden;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
        }
        .seg-control a {
            height:100%;
            min-width:42px;
            padding:0 12px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            color:#2f3943;
            font-size:12px;
            font-weight:950;
            border-right:1px solid var(--line);
            white-space:nowrap;
        }
        .seg-control a:last-child { border-right:0; }
        .seg-control a.is-active {
            background:var(--dark);
            color:#fff;
        }
        .auth-seg a { min-width:72px; }
        .account-btn { max-width:168px; padding-left:8px; }
        .account-btn > span:last-child { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .account-mini { width:26px; height:26px; display:grid; place-items:center; flex:0 0 26px; border-radius:50%; background:#eaf1ff; color:#0d6f7f; font-size:12px; font-weight:950; }

        .cart-btn em {
            min-width:20px;
            height:20px;
            display:grid;
            place-items:center;
            border-radius:50%;
            background:#00a6a6;
            color:#fff;
            font-size:11px;
            font-style:normal;
        }
        .account-drop {
            position:absolute;
            top:calc(100% + 8px);
            right:0;
            z-index:60;
            width:250px;
            padding:13px;
            display:none;
            gap:8px;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            box-shadow:var(--shadow, 0 18px 44px rgba(24,33,44,.14));
        }

        .account-wrap:hover .account-drop,
        .account-wrap:focus-within .account-drop { display:grid; }
        .account-drop small { color:var(--muted); font-size:10px; font-weight:950; text-transform:uppercase; letter-spacing:.8px; }
        .account-drop strong { font-size:13px; }
        .account-drop span { color:var(--muted); font-size:12px; font-weight:750; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .account-drop a { min-height:34px; display:flex; align-items:center; justify-content:center; border:1px solid var(--line); border-radius:var(--radius); background:#f7fafc; color:var(--dark); font-size:12px; font-weight:950; }
        .account-drop form { margin:6px 0 0; }
        .account-drop button { width:100%; height:36px; border:0; border-radius:var(--radius); background:var(--dark); color:#fff; font:inherit; font-size:12px; font-weight:950; cursor:pointer; }

        .catalog-overlay {
            position:fixed;
            inset:0;
            z-index:140;
            display:none;
            padding:8px;
            overflow:hidden;
            background:rgba(8,13,20,.56);
            overscroll-behavior:contain;
        }

        body.catalog-open { overflow:hidden; touch-action:none; }
        
        body.catalog-open .catalog-overlay {
            display:block;
            opacity:1;
        }
        
        .catalog-overlay {
            position:fixed;
            inset:0;
            z-index:140;
            display:none;
            background:rgba(8,13,20,.56);
            opacity:0;
            transition:opacity .28s ease;
        }

        body.catalog-open .catalog-overlay {
            display:block;
            opacity:1;
        }

        .catalog-shell {
            width:min(1320px, calc(100vw - 24px));
            height:min(920px, calc(100dvh - 24px));
            margin:12px;

            display:grid;
            grid-template-columns:300px minmax(0, 1fr);

            overflow:hidden;
            border-radius:14px;
            background:#fff;
            box-shadow:0 30px 80px rgba(0,0,0,.35);

            transform:translateX(-80px) scale(.98);
            opacity:0;

            transition:
                transform .34s cubic-bezier(.22,.9,.24,1),
                opacity .24s ease;
        }

        body.catalog-open .catalog-shell {
            transform:translateX(0) scale(1);
            opacity:1;
}

        .catalog-shell {
            float:left;
            width:1320px;
            max-width:none;
            height:min(920px, calc(100dvh - 16px));
            margin:0 auto;
            display:grid;
            grid-template-columns:300px minmax(0, 1fr);
            overflow:hidden;
            border-radius:14px;
            background:#fff;
            box-shadow:0 30px 80px rgba(0,0,0,.35);
            transform-origin:top center;
            zoom:1;
        }

        .drawer-side {
            min-width:0;
            display:flex;
            flex-direction:column;
            background:#101820;
            border-right:1px solid rgba(255,255,255,.08);
            color:#fff;
            border-radius:14px 0 0 14px;
            min-height:0;
        }

        .drawer-top {
            min-height:76px;
            padding:14px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            background:#121d27;
            border-bottom:1px solid rgba(255,255,255,.08);
        }

        .drawer-top .brand span span { color:#a7b4c2; }

        .drawer-close {
            display:contents;
            width:40px;
            height:40px;
            border:1px solid rgba(255,255,255,.18);
            border-radius:var(--radius);
            background:rgba(255,255,255,.06);
            color:#fff;
            font-size:24px;
            line-height:1;
        }

        .cat-icon {
            display:grid;
            place-items:center;
            border-radius:10px;
            background:rgba(0,166,166,.16);
            color:#7dd3fc;
            font-weight:900;
        }

        .drawer-list { flex:1 1 auto; min-height:0; padding:10px; overflow:auto; overscroll-behavior:contain; scrollbar-width:thin; }

        .drawer-cat-button {
            width:100%;
            min-height:50px;
            display:grid;
            grid-template-columns:36px minmax(0, 1fr) auto;
            align-items:center;
            gap:10px;
            margin-bottom:5px;
            padding:7px 9px;
            border:1px solid transparent;
            border-radius:10px;
            background:transparent;
            color:#d8e2ec;
            text-align:left;
        }

        .drawer-cat-button:hover,
        .drawer-cat-button.is-active {
            background:#fff;
            border-color:#fff;
            color:#0d6f7f;
            box-shadow:0 12px 24px rgba(0,0,0,.18);
        }

        .cat-icon { width:36px; height:36px; font-size:13px; }
        .cat-name { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:13px; font-weight:900; }
        .cat-count { color:currentColor; opacity:.8; font-size:18px; font-weight:900; }
        .drawer-main { min-width:0; min-height:0; overflow:hidden; scrollbar-width:thin; background:#f4f7fa; border-radius:0 14px 14px 0; }
        .drawer-panel { display:none; min-height:0; height:100%; }
        .drawer-panel.is-active { display:block; }
        .panel-content { height:100%; min-height:0; overflow:auto; overscroll-behavior:contain; padding:26px; scrollbar-width:thin; }
        .panel-head { position:static; display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin:0 0 20px; padding:0 0 16px; border-bottom:1px solid #e1e8ef; background:#f4f7fa; }
        .panel-head h2 { margin:0; font-size:29px; line-height:1.05; letter-spacing:-.7px; }
        .panel-head p { margin:8px 0 0; color:var(--muted); font-size:13px; font-weight:700; }
        .view-all { min-height:39px; display:inline-flex; align-items:center; justify-content:center; padding:0 14px; border-radius:var(--radius); background:var(--green-2); color:#fff; font-size:12px; font-weight:900; }
        .subcat-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:9px; }
        .subcat-card { min-height:96px; padding:12px; display:flex; flex-direction:column; justify-content:space-between; border:1px solid var(--line); border-radius:var(--radius); background:linear-gradient(135deg,#fff 0%,#f8fbfd 100%); color:#18212c; overflow:hidden; }
        .subcat-card:hover { border-color:#bdd8c8; box-shadow:var(--shadow-soft, 0 10px 24px rgba(24,33,44,.08)); transform:translateY(-1px); }
        .subcat-card strong { margin-top:6px; font-size:14px; line-height:1.18; overflow-wrap:anywhere; }
        .subcat-kicker { color:#0d6f7f; font-size:9px; font-weight:950; letter-spacing:.8px; text-transform:uppercase; }
        .subcat-meta { display:flex; align-items:center; justify-content:space-between; gap:8px; margin-top:10px; color:var(--muted); font-size:12px; font-weight:800; }
        .subcat-meta span { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .subcat-meta em { width:24px; height:24px; flex:0 0 24px; display:grid; place-items:center; border-radius:50%; background:#eaf1ff; color:#0d6f7f; font-style:normal; font-size:15px; font-weight:950; }
        .mega-category-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:20px 26px; align-items:start; padding-bottom:24px; }
        .mega-group { min-width:0; padding:0 0 14px; border-bottom:1px solid #dfe7ef; }
        .mega-group-head,
        .mega-leaf { min-height:30px; display:flex; align-items:center; justify-content:space-between; gap:10px; color:#17212c; font-size:13px; line-height:1.22; font-weight:950; text-transform:uppercase; letter-spacing:.2px; }
        .mega-group-head { margin-bottom:8px; }
        .mega-leaf { min-height:36px; padding:0 0 10px; border-bottom:1px solid #dfe7ef; }
        .mega-group-head:hover,
        .mega-leaf:hover { color:#0d6f7f; }
        .mega-group-head em,
        .mega-leaf em { width:22px; height:22px; flex:0 0 22px; display:grid; place-items:center; border-radius:50%; background:#eaf1ff; color:#0d6f7f; font-style:normal; font-size:14px; font-weight:950; }
        .mega-links { display:grid; gap:1px; }
        .mega-links a { min-height:25px; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:3px 0; color:#596879; font-size:12px; line-height:1.25; font-weight:750; }
        .mega-links a:hover { color:#0d6f7f; text-decoration:underline; text-underline-offset:3px; }
        .mega-links span { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .mega-links small { color:#9aa5b1; font-size:10px; font-weight:900; white-space:nowrap; }

        .drawer-promo {
            padding:22px 14px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            gap:18px;
            overflow:auto;
            text-align:center;
            border-left:1px solid var(--line);
            background:linear-gradient(180deg, #f7fbff 0%, #eef6f4 100%);
        }

        .promo-icon { width:76px; height:76px; margin:12px auto 16px; display:grid; place-items:center; border-radius:18px; background:rgba(47,111,237,.09); color:var(--green-2); font-size:30px; font-weight:900; }
        .drawer-promo small { color:#0d6f7f; font-size:12px; font-weight:900; text-transform:uppercase; letter-spacing:1.5px; }
        .drawer-promo h3 { margin:8px 0 0; white-space:pre-line; color:#18212c; font-size:25px; line-height:1.05; }
        .drawer-promo p { margin:15px auto 0; max-width:215px; color:#5f6f64; font-size:12px; line-height:1.5; font-weight:600; }
        .promo-links { display:grid; gap:9px; }
        .promo-links a { min-height:36px; display:grid; place-items:center; border-radius:var(--radius); background:var(--green-2); color:#fff; font-size:10px; font-weight:900; text-transform:uppercase; padding:0 8px; }
        .promo-links a:nth-child(n+2) { background:#34454d; }

        @media (max-width:1344px) and (min-width:781px) {
            .catalog-shell { zoom:.96; }
        }

        @media (max-width:1240px) and (min-width:781px) {
            .catalog-shell { zoom:.88; }
        }

        @media (max-width:1120px) and (min-width:781px) {
            .catalog-shell { zoom:.8; }
        }

        @media (max-width:980px) and (min-width:781px) {
            .catalog-shell { zoom:.7; }
        }

        @media (max-height:820px) and (min-width:1241px) {
            .catalog-shell { zoom:.9; }
        }

        @media (max-height:730px) and (min-width:1121px) {
            .catalog-shell { zoom:.8; }
        }

        @media (max-width:1180px) {
            .header-main { grid-template-columns:145px 1fr minmax(220px, 1fr); }
            .search-box { grid-column:1 / -1; order:5; }
            .site-header .wrap { padding:12px 0; }
            .header-main { height:auto; }
            .site-header .header-actions { justify-content:flex-end; }
        }

        @media (max-width:780px) {
            .call-wrap { display:none; }
            .header-main { grid-template-columns:120px 1fr; gap:10px; }
            .products-trigger { height:42px; font-size:12px; }
            .site-header .brand strong { font-size:18px; }
            .icon-btn:not(.cart-btn):not(.icon-only):not(.account-btn),
            .auth-seg { display:none; }
            .catalog-overlay { padding:0; overflow:auto; }
            .catalog-shell { width:100vw; max-width:100vw; min-height:100dvh; height:auto; margin:0; grid-template-columns:1fr; grid-template-rows:auto auto; overflow:visible; border-radius:0; zoom:1; }
            .drawer-side { max-height:none; border-right:0; border-bottom:1px solid rgba(255,255,255,.08); border-radius:0; }
            .drawer-top { min-height:68px; }
            .drawer-list { display:flex; gap:8px; overflow-x:auto; padding:0 12px 12px; }
            .drawer-cat-button { min-width:218px; margin-bottom:0; }
            .drawer-main { min-height:0; overflow:visible; border-radius:0; }
            .drawer-panel.is-active { display:block; }
            .drawer-promo { display:none; }
            .subcat-grid,
            .mega-category-grid { grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); }
        }

        @media (max-width:520px) {
            .header-main { grid-template-columns:1fr; }
            .products-trigger { width:100%; }
            .site-header .header-actions { justify-content:space-between; }
            .panel-content { padding:18px; }
            .panel-head { display:block; }
            .panel-head { margin:0 0 16px; padding:0 0 14px; }
            .view-all { margin-top:14px; }
            .subcat-grid,
            .mega-category-grid { grid-template-columns:1fr; }
            .mega-group { padding-bottom:12px; }
        }

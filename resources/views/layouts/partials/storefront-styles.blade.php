  <style>[x-cloak]{display:none!important;}</style>

  <style>
    .card-img { aspect-ratio: 4/3; object-fit: cover; width: 100%; height: 100%; }

   
    .ps-glass-panel {
      background: rgba(255,255,255,.62);
      border: 0;
      box-shadow: 0 12px 32px rgba(15,23,42,.08);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    .ps-section-panel {
      background: rgba(255,255,255,.58);
      border: 0;
      border-radius: 2rem;
      box-shadow: 0 16px 40px rgba(15,23,42,.08);
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }

    .ps-product-card {
      position: relative;
      overflow: hidden;
      border-radius: 1.65rem;
      background: rgba(255,255,255,.94);
      border: 0;
      box-shadow: 0 14px 34px rgba(15,23,42,.08);
      transition: transform .22s ease, box-shadow .22s ease, background .22s ease;
    }
    .ps-product-card::before {
      content: '';
      position: absolute;
      inset: 0;
      pointer-events: none;
      border-radius: inherit;
      background: linear-gradient(135deg, rgba(168,85,247,.16), rgba(59,130,246,.06) 42%, rgba(255,255,255,0) 75%);
      opacity: .75;
    }
    .ps-product-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 22px 54px rgba(15,23,42,.14);
      background: rgba(255,255,255,.98);
    }

    .ps-product-media {
      position: relative;
      aspect-ratio: 1/1;
      margin: .75rem;
      overflow: hidden;
      border-radius: 1.25rem;
      background: linear-gradient(135deg, rgba(250,245,255,.95), rgba(255,255,255,.95));
      border: 1px solid rgba(168,85,247,.18);
      box-shadow: inset 0 0 0 8px rgba(216,180,254,.42);
    }

    .ps-product-media img {
      background: transparent !important;
      transition: transform .28s ease;
    }
    .ps-product-card:hover .ps-product-media img {
      transform: scale(1.025);
    }

    .ps-chip {
      border-radius: 9999px;
      background: rgba(255,255,255,.9);
      border: 1px solid rgba(226,232,240,.95);
      box-shadow: 0 10px 24px rgba(15,23,42,.06);
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .ps-chip:hover {
      transform: translateY(-2px);
      border-color: rgba(168,85,247,.35);
      box-shadow: 0 16px 32px rgba(15,23,42,.1);
    }

    .ps-category-card {
      border-radius: 1.5rem;
      background: rgba(255,255,255,.9);
      border: 0;
      box-shadow: 0 14px 34px rgba(15,23,42,.08);
      transition: transform .2s ease, box-shadow .2s ease;
    }
    .ps-category-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 46px rgba(15,23,42,.12);
    }


    .ps-btn-light,
    .ps-btn-soft,
    .ps-btn-primary {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .45rem;
      border-radius: 9999px;
      font-weight: 800;
      transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease, opacity .18s ease;
      text-decoration: none;
    }

    .ps-btn-light {
      background: rgba(229,236,244,.56);
      color: rgb(15,23,42);
      border: 1.5px solid rgba(255,255,255,.28);
      box-shadow: 0 10px 24px rgba(15,23,42,.10), inset 0 1px 0 rgba(255,255,255,.10);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    .ps-btn-light:hover {
      transform: translateY(-1px);
      box-shadow: 0 16px 32px rgba(15,23,42,.14), inset 0 1px 0 rgba(255,255,255,.12);
      background: rgba(229,236,244,.68);
      color: rgb(15,23,42);
    }

    .ps-btn-soft {
      background: rgba(222,231,240,.44);
      color: rgb(15,23,42);
      border: 1.5px solid rgba(255,255,255,.22);
      box-shadow: 0 8px 20px rgba(15,23,42,.08), inset 0 1px 0 rgba(255,255,255,.08);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    .ps-btn-soft:hover {
      transform: translateY(-1px);
      box-shadow: 0 14px 28px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.10);
      background: rgba(222,231,240,.58);
      color: rgb(15,23,42);
    }

    .ps-btn-primary {
      color: white;
      background: linear-gradient(135deg, rgb(124,58,237), rgb(147,51,234));
      box-shadow: 0 12px 28px rgba(124,58,237,.28);
    }
    .ps-btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 18px 36px rgba(124,58,237,.34);
      filter: saturate(1.03);
    }

    .ps-btn-md { padding: .8rem 1.35rem; font-size: .92rem; }
    .ps-btn-sm { padding: .7rem 1.1rem; font-size: .86rem; }
    .ps-btn-xs { padding: .55rem .95rem; font-size: .76rem; }
    .ps-btn-light:focus-visible,
    .ps-btn-soft:focus-visible,
    .ps-btn-primary:focus-visible {
      outline: 3px solid rgba(168,85,247,.35);
      outline-offset: 3px;
    }



    /* Payment visual system  */
    /* orders visible flow theme. */
    .ps-flow-page-title {
      display:inline-flex;
      align-items:center;
      gap:.65rem;
      border-radius:9999px;
      padding:.7rem 1.05rem;
      background:rgba(30,41,59,.10);
      border:1px solid rgba(255,255,255,.34);
      box-shadow:0 14px 34px rgba(15,23,42,.10), inset 0 1px 0 rgba(255,255,255,.16);
      backdrop-filter:blur(16px);
      -webkit-backdrop-filter:blur(16px);
    }

    .ps-flow-card {
      position:relative;
      overflow:hidden;
      border-radius:2rem;
      background:rgba(241,245,249,.74);
      border:1px solid rgba(100,116,139,.14);
      box-shadow:0 18px 46px rgba(15,23,42,.12), inset 0 1px 0 rgba(255,255,255,.28);
      backdrop-filter:blur(18px);
      -webkit-backdrop-filter:blur(18px);
    }
    .ps-flow-card::before {
      content:'';
      position:absolute;
      top:0;
      left:1.25rem;
      right:1.25rem;
      height:5px;
      border-radius:0 0 999px 999px;
      background:linear-gradient(90deg, rgba(124,58,237,.95), rgba(56,189,248,.72), rgba(255,255,255,0));
      pointer-events:none;
    }
    .ps-flow-card h2 {
      display:flex;
      align-items:center;
      gap:.55rem;
    }
    .ps-flow-card h2::before {
      content:'';
      width:.62rem;
      height:.62rem;
      border-radius:999px;
      background:linear-gradient(135deg, rgb(124,58,237), rgb(56,189,248));
      box-shadow:0 0 0 4px rgba(168,85,247,.10);
      flex:0 0 auto;
    }

    .ps-flow-subcard {
      position:relative;
      border-radius:1.5rem;
      background:rgba(255,255,255,.62);
      border:1px solid rgba(100,116,139,.12);
      box-shadow:0 10px 24px rgba(15,23,42,.07), inset 0 1px 0 rgba(255,255,255,.24);
      backdrop-filter:blur(12px);
      -webkit-backdrop-filter:blur(12px);
    }

    .ps-flow-product-row {
      position:relative;
      display:flex;
      gap:1rem;
      border-radius:1.5rem;
      background:rgba(255,255,255,.62);
      border:1px solid rgba(100,116,139,.14);
      box-shadow:0 12px 28px rgba(15,23,42,.075), inset 0 1px 0 rgba(255,255,255,.28);
      padding:1rem;
      backdrop-filter:blur(14px);
      -webkit-backdrop-filter:blur(14px);
    }
    .ps-flow-product-row::before {
      content:'';
      position:absolute;
      left:0;
      top:1rem;
      bottom:1rem;
      width:4px;
      border-radius:999px;
      background:linear-gradient(180deg, rgba(124,58,237,.7), rgba(56,189,248,.55));
    }

    .ps-flow-input {
      width:100%;
      border-radius:1.05rem;
      border:1px solid rgba(100,116,139,.20);
      background:rgba(255,255,255,.76);
      padding:.88rem 1rem;
      outline:none;
      box-shadow:inset 0 1px 0 rgba(255,255,255,.36), 0 8px 18px rgba(15,23,42,.035);
      transition:box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .ps-flow-input:focus {
      border-color:rgba(168,85,247,.52);
      background:rgba(255,255,255,.96);
      box-shadow:0 0 0 4px rgba(168,85,247,.14), inset 0 1px 0 rgba(255,255,255,.42);
    }

    .ps-flow-pill {
      display:inline-flex;
      align-items:center;
      justify-content:center;
      border-radius:9999px;
      padding:.48rem .85rem;
      font-size:.75rem;
      font-weight:900;
      color:rgb(91,33,182);
      background:rgba(243,232,255,.82);
      border:1px solid rgba(192,132,252,.45);
      box-shadow:0 9px 20px rgba(88,28,135,.09);
    }

    .ps-flow-primary-button {
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:.5rem;
      width:100%;
      border-radius:1.1rem;
      background:linear-gradient(135deg, rgb(109,40,217), rgb(147,51,234));
      color:white;
      font-weight:900;
      padding:.95rem 1rem;
      box-shadow:0 18px 36px rgba(124,58,237,.30);
      transition:transform .18s ease, box-shadow .18s ease, filter .18s ease, opacity .18s ease;
    }
    .ps-flow-primary-button:hover { transform:translateY(-1px); box-shadow:0 22px 44px rgba(124,58,237,.35); filter:saturate(1.04); }
    .ps-flow-primary-button:disabled { cursor:not-allowed; opacity:.45; transform:none; box-shadow:none; }

    .ps-flow-secondary-button {
      border-radius:1.1rem;
      background:linear-gradient(135deg, rgba(109,40,217,.96), rgba(147,51,234,.96));
      color:white;
      font-weight:900;
      box-shadow:0 16px 32px rgba(124,58,237,.25);
      transition:transform .18s ease, box-shadow .18s ease;
    }
    .ps-flow-secondary-button:hover { transform:translateY(-1px); box-shadow:0 20px 38px rgba(124,58,237,.30); }

    .ps-flow-total-box {
      border-radius:1.45rem;
      background:rgba(250,245,255,.78);
      border:1px solid rgba(192,132,252,.34);
      box-shadow:0 10px 24px rgba(88,28,135,.07), inset 0 1px 0 rgba(255,255,255,.32);
    }

    .ps-flow-alert-success { border-radius:1.25rem; border:1px solid rgba(74,222,128,.38); background:rgba(220,252,231,.86); color:rgb(21,128,61); box-shadow:0 10px 22px rgba(21,128,61,.06); }
    .ps-flow-alert-danger { border-radius:1.25rem; border:1px solid rgba(252,165,165,.48); background:rgba(254,242,242,.88); color:rgb(185,28,28); box-shadow:0 10px 22px rgba(185,28,28,.06); }
    .ps-flow-alert-amber { border-radius:1.25rem; border:1px solid rgba(251,191,36,.44); background:rgba(255,251,235,.88); color:rgb(180,83,9); box-shadow:0 10px 22px rgba(180,83,9,.06); }
    .ps-flow-alert-info { border-radius:1.25rem; border:1px solid rgba(125,211,252,.44); background:rgba(240,249,255,.88); color:rgb(3,105,161); box-shadow:0 10px 22px rgba(3,105,161,.06); }

  </style>

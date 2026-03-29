<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Under Maintenance — {$shop.name|escape:'htmlall':'UTF-8'}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;1,300&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
  <style>
    {literal}
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'DM Sans',sans-serif;background:#f4f6f2;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px;}
    .card{background:#fff;border-radius:16px;border:1px solid #e5e7eb;padding:56px 48px;max-width:480px;text-align:center;box-shadow:0 12px 40px rgba(0,0,0,.08);}
    .icon{font-size:3rem;margin-bottom:24px;}
    h1{font-family:'Cormorant Garamond',serif;font-weight:400;font-size:2.2rem;color:#111827;margin-bottom:12px;}
    p{font-size:14px;color:#6b7280;line-height:1.7;}
    .shop{font-family:'Cormorant Garamond',serif;font-size:1.4rem;color:#4a7c59;margin-bottom:32px;}
    {/literal}
  </style>
</head>
<body>
  <div class="card">
    <div class="icon">🌿</div>
    <div class="shop">{$shop.name|escape:'htmlall':'UTF-8'}</div>
    <h1>{l s="We're tending to the garden." mod='fsl'}</h1>
    <p>{l s="Our store is temporarily closed for maintenance. We'll be back shortly — thank you for your patience." mod='fsl'}</p>
    {if $maintenance_text}
      <p style="margin-top:16px;font-style:italic;color:#9ca3af">{$maintenance_text nofilter}</p>
    {/if}
  </div>
</body>
</html>

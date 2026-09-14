{{--
  Brass Art Gallery partial. The CSS and JS are external so this view works with a strict CSP.
  Prebuilt bundle from the vendor handoff — copy public/vendor/brass-showroom exactly if updating.
--}}
<link rel="stylesheet" href="{{ asset('vendor/brass-showroom/brass-showroom.css') }}">

@php
  $encodedShowroomConfig = isset($showroomConfig)
      ? base64_encode(json_encode($showroomConfig, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))
      : '';
@endphp
<section id="brass-showroom" class="bs-root" data-assets-base="{{ asset('vendor/brass-showroom/images') }}" data-inline-config="{{ $encodedShowroomConfig }}" aria-label="ကြေးဝါအနုပညာပြခန်း">
  <div class="bs-room" data-bs-room role="img" aria-label="လှည့်လည်ကြည့်ရှုနိုင်သော 3D ပြခန်း"></div>

  <header class="bs-topbar">
    <a href="{{ url('/') }}" class="bs-mark" aria-label="U Nyi Lay Silver Shop">▱</a>
    <div class="bs-brand"><strong>BRASS ART GALLERY</strong><span>ကြေးဝါအနုပညာပြခန်း</span></div>
    <div class="bs-actions">
      <a href="{{ url('/') }}" class="bs-pill" aria-label="Back to shop">← Shop</a>
      <button type="button" class="bs-pill" data-bs-language aria-label="English သို့ ပြောင်းရန်">EN <i>/</i> မြန်မာ</button>
      <button type="button" class="bs-circle" data-bs-help aria-label="အသုံးပြုနည်း">?</button>
    </div>
  </header>

  <section class="bs-welcome" data-bs-welcome>
    <span class="bs-eyebrow">THE COLLECTION · 01—06</span>
    <h1 data-i18n="heroTitle">ကြေးဝါအနုပညာ၏ အလှ</h1>
    <p data-i18n="heroCopy">မြန်မာ့လက်ရာတွေကို နီးနီးကပ်ကပ် ခံစားကြည့်ရှုပါ။</p>
    <button type="button" class="bs-primary" data-bs-enter disabled><span data-i18n="loading">ပြခန်းကို ပြင်ဆင်နေသည်…</span><b>↗</b></button>
    <small data-i18n="hint">ဖိဆွဲ၍ လှည့်ကြည့် · ခလုတ်ဖြင့် လမ်းလျှောက်</small>
    <button type="button" class="bs-link" data-bs-browse data-i18n="browse">ပန်းချီများကို တိုက်ရိုက်ကြည့်ရန်</button>
  </section>

  <div class="bs-inside" data-bs-inside hidden>
    <div class="bs-status"><i></i><span data-i18n="inside">ပြခန်းအတွင်း</span><b></b>01—06</div>
    <div class="bs-crosshair" aria-hidden="true"></div>
    <div class="bs-tools">
      <button type="button" data-bs-reset><span>↶</span><span data-i18n="entrance">ဝင်ပေါက်သို့</span></button>
      <button type="button" data-bs-browse><span>▦</span><span data-i18n="artworks">ပန်းချီများ</span></button>
    </div>
    <div class="bs-joystick-wrap">
      <div class="bs-joystick" data-bs-joystick role="group" aria-label="လမ်းလျှောက်ခလုတ်"><i class="up">↑</i><i class="down">↓</i><i class="left">←</i><i class="right">→</i><b data-bs-knob>✣</b></div>
      <span data-i18n="walk">လမ်းလျှောက်ရန်</span>
    </div>
    <div class="bs-look"><b>⌁</b><span data-i18n="look">ဖိဆွဲ၍ လှည့်ကြည့်ပါ</span><small data-i18n="selectHint">ပန်းချီကို နှိပ်၍ အနီးကပ်ကြည့်ပါ</small></div>
    <div class="bs-keys"><kbd>W</kbd><span><kbd>A</kbd><kbd>S</kbd><kbd>D</kbd></span><small>MOVE · Q / E TURN</small></div>
  </div>

  <section class="bs-catalog" data-bs-catalog hidden>
    <div class="bs-catalog-head"><div><span class="bs-eyebrow">THE COLLECTION · 06 WORKS</span><h1 data-i18n="collection">ကြေးဝါလက်ရာများ</h1><p data-bs-fallback hidden data-i18n="fallback">ဒီစက်မှာ 3D ပြခန်းမဖွင့်နိုင်သဖြင့် ပန်းချီများကို ဒီနေရာမှာ ကြည့်နိုင်ပါတယ်။</p></div><button type="button" class="bs-primary" data-bs-enter><span data-i18n="enter">ပြခန်းထဲ ဝင်ကြည့်မယ်</span><b>↗</b></button></div>
    <div class="bs-grid" data-bs-grid></div>
  </section>

  <footer class="bs-footer"><span>MYANMAR · BRASS COLLECTION</span><span data-i18n="works">လက်ရာ ၆ ပုံ</span></footer>

  <dialog class="bs-dialog bs-art-dialog" data-bs-art-dialog aria-labelledby="bs-art-title">
    <button type="button" class="bs-dialog-close" data-bs-close aria-label="ပိတ်မည်">×</button>
    <div class="bs-detail-art" data-bs-detail-art></div>
    <div class="bs-detail-copy"><span class="bs-eyebrow" data-bs-number></span><h2 id="bs-art-title" data-bs-title></h2><p class="bs-alt" data-bs-alt></p><p class="bs-muted" data-i18n="medium">မြန်မာ့ကြေးဝါပန်းချီ</p><dl><div><dt data-i18n="frameSize">ဘောင်အပါအဝင် အရွယ်အစား</dt><dd data-bs-size></dd></div><div><dt data-i18n="price">ဈေးနှုန်း</dt><dd data-bs-price>၂၀၀,၀၀၀ ကျပ်</dd></div></dl><nav aria-label="ပန်းချီရွေးရန်"><button type="button" data-bs-previous aria-label="ရှေ့ပုံ">←</button><span data-bs-counter></span><button type="button" data-bs-next aria-label="နောက်ပုံ">→</button></nav><button type="button" class="bs-primary" data-bs-close><span data-i18n="continue">ဆက်လက်ကြည့်ရှုမယ်</span><b>↗</b></button></div>
  </dialog>

  <dialog class="bs-dialog bs-help-dialog" data-bs-help-dialog aria-labelledby="bs-help-title">
    <button type="button" class="bs-dialog-close" data-bs-close aria-label="ပိတ်မည်">×</button><span class="bs-eyebrow">YOUR VISIT</span><h2 id="bs-help-title" data-i18n="helpTitle">ပြခန်းကို လှည့်ပတ်ကြည့်ရှုရန်</h2><p data-i18n="helpCopy">အေးအေးဆေးဆေး လှည့်ပတ်ပြီး လက်ရာအသေးစိတ်တွေကို ကြည့်ရှုပါ။</p><div class="bs-help-lines"><p><b>✣</b><span data-i18n="helpMove">WASD / မြားခလုတ်များ သို့မဟုတ် စက်ဝိုင်းခလုတ်ကို ဖိဆွဲပြီး လမ်းလျှောက်ပါ။</span></p><p><b>⌁</b><span data-i18n="helpLook">မျက်နှာပြင်ကို ဖိဆွဲ၍ လှည့်ကြည့်ပါ။ Keyboard မှ Q / E ကိုလည်း သုံးနိုင်ပါတယ်။</span></p><p><b>↗</b><span data-i18n="helpArt">ပန်းချီကို နှိပ်ပြီး အနီးကပ်ပုံ၊ အရွယ်အစားနဲ့ ဈေးနှုန်းကို ကြည့်ပါ။</span></p></div><button type="button" class="bs-primary" data-bs-close><span data-i18n="gotIt">နားလည်ပါပြီ</span><b>↗</b></button>
  </dialog>

  <noscript><p class="bs-noscript">This virtual showroom requires JavaScript. / ဤပြခန်းအတွက် JavaScript လိုအပ်ပါသည်။</p></noscript>
</section>

<script defer src="{{ asset('vendor/brass-showroom/brass-showroom.js') }}"></script>

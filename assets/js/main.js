
document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.querySelector(".menu-toggle");
  const nav = document.querySelector(".navlinks");
  if (toggle && nav) toggle.addEventListener("click", () => nav.classList.toggle("open"));

  const path = location.pathname.split("/").pop() || "index.html";
  document.querySelectorAll(".navlinks a").forEach(a => {
    if (a.getAttribute("href") === path) a.classList.add("active");
  });

  document.querySelectorAll("[data-year]").forEach(el => el.textContent = new Date().getFullYear());

  document.querySelectorAll('input[name="ts"]').forEach(inp => {
    inp.value = String(Math.floor(Date.now() / 1000));
  });

  document.querySelectorAll("form[data-demo]").forEach(form => {
    form.addEventListener("submit", e => {
      e.preventDefault();
      const msg = form.querySelector(".form-message");
      if (msg) {
        msg.textContent = "Merci. Votre demande a bien été préparée. Connectez ce formulaire à votre CRM ou à votre adresse e-mail avant la mise en production.";
        msg.style.display = "block";
      }
    });
  });
});

/* BDJ Consulting — V5 robust customer-centric carousel */
document.addEventListener('DOMContentLoaded', function () {
  const carousel = document.querySelector('[data-carousel]');
  if (!carousel) return;

  const track = carousel.querySelector('.customer-track');
  const slides = [...carousel.querySelectorAll('.customer-slide')];
  const dots = [...carousel.querySelectorAll('.carousel-dot')];
  const prev = carousel.querySelector('.carousel-prev');
  const next = carousel.querySelector('.carousel-next');

  if (!track || slides.length === 0) return;

  let current = 0;
  let timer = null;
  const INTERVAL = 5500;

  function render(index) {
    current = ((index % slides.length) + slides.length) % slides.length;
    track.style.transform = `translate3d(-${current * 100}%,0,0)`;
    track.style.transition = 'transform 600ms cubic-bezier(.22,.61,.36,1)';
    dots.forEach((dot, i) => dot.classList.toggle('active', i === current));
  }

  function stop() {
    if (timer !== null) {
      window.clearInterval(timer);
      timer = null;
    }
  }

  function start() {
    stop();
    timer = window.setInterval(() => render(current + 1), INTERVAL);
  }

  function restart() {
    stop();
    start();
  }

  prev?.addEventListener('click', () => {
    render(current - 1);
    restart();
  });

  next?.addEventListener('click', () => {
    render(current + 1);
    restart();
  });

  dots.forEach((dot, i) => {
    dot.addEventListener('click', () => {
      render(i);
      restart();
    });
  });

  carousel.addEventListener('mouseenter', stop);
  carousel.addEventListener('mouseleave', start);

  // Keyboard accessibility
  carousel.addEventListener('keydown', e => {
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      render(current - 1);
      restart();
    }
    if (e.key === 'ArrowRight') {
      e.preventDefault();
      render(current + 1);
      restart();
    }
  });
  carousel.setAttribute('tabindex', '0');

  // Mobile swipe
  let touchStartX = null;
  carousel.addEventListener('touchstart', e => {
    touchStartX = e.touches[0].clientX;
    stop();
  }, {passive:true});

  carousel.addEventListener('touchend', e => {
    if (touchStartX === null) return;
    const delta = e.changedTouches[0].clientX - touchStartX;
    if (Math.abs(delta) > 45) render(delta < 0 ? current + 1 : current - 1);
    touchStartX = null;
    start();
  }, {passive:true});

  // If a Pexels image fails, fall back to another known diverse call-center image.
  carousel.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function () {
      const fallback = 'https://images.pexels.com/photos/8867249/pexels-photo-8867249.jpeg?auto=compress&cs=tinysrgb&w=1200';
      if (this.src !== fallback) this.src = fallback;
    });
  });

  render(0);
  start();
});


/* ==========================================================
   BDJ V6.1.4 — COOKIE CONSENT
   ========================================================== */
document.addEventListener('DOMContentLoaded', function(){
  const box = document.getElementById('cookie-consent');
  if(!box) return;

  const key = 'bdj_cookie_choice_v2';
  let choice = null;
  try { choice = localStorage.getItem(key); } catch(e) {}

  if(!choice) box.classList.add('show');

  function save(value){
    try { localStorage.setItem(key, value); } catch(e) {}
    box.classList.remove('show');
  }

  const accept = document.getElementById('cookie-accept');
  const settings = document.getElementById('cookie-settings');

  if(accept) accept.addEventListener('click', function(){
    save('accepted');
  });

  if(settings) settings.addEventListener('click', function(){
    window.location.href = 'politique-confidentialite.html#cookies';
  });
});

/* ==========================================================
   WHATSAPP CHAT WIDGET — V2 (all enhancements)
   ========================================================== */
document.addEventListener('DOMContentLoaded', function(){
  var bubble = document.getElementById('wa-chat-bubble');
  var box = document.getElementById('wa-chat-box');
  var close = document.getElementById('wa-chat-close');
  var input = document.getElementById('wa-chat-input');
  var send = document.getElementById('wa-chat-send');
  var body = document.getElementById('wa-chat-body');
  var badge = document.getElementById('wa-chat-badge');
  if(!bubble || !box) return;

  var waNumber = '2250102440707';
  var userName = '';
  try { userName = localStorage.getItem('wa_user_name') || ''; } catch(e){}

  /* --- Business hours: Mon-Fri 8h-18h (WAT UTC+1) --- */
  function isOnline(){
    var now = new Date();
    var utcHour = now.getUTCHours();
    var utcDay = now.getUTCDay();
    var watHour = (utcHour + 1) % 24;
    return utcDay >= 1 && utcDay <= 5 && watHour >= 8 && watHour < 18;
  }

  /* --- Page-aware welcome messages --- */
  function getWelcomeMessage(){
    var path = location.pathname.split('/').pop() || 'index.html';
    var map = {
      'contact.html':'Besoin d\'un devis ? Parlons-en !',
      'solutions.html':'Intéressé par nos solutions ? Comment pouvons-nous vous aider ?',
      'carrieres.html':'Vous souhaitez rejoindre notre équipe ?',
      'a-propos.html':'Envie d\'en savoir plus sur BDJ Consulting ?',
      'secteurs.html':'Vous cherchez un partenaire pour votre secteur ?'
    };
    return map[path] || 'Comment pouvons-nous vous aider ?';
  }

  /* --- Quick replies --- */
  var quickReplies = [
    'Je voudrais un devis',
    'Vos services',
    'Horaires d\'ouverture',
    'Postuler maintenant',
    'Autre demande'
  ];

  /* --- Show badge on load --- */
  if(badge) badge.style.display = 'block';

  /* --- Toggle chat --- */
  bubble.addEventListener('click', function(){
    box.classList.toggle('open');
    if(box.classList.contains('open')){
      if(badge) badge.style.display = 'none';
      input.focus();
      if(body.children.length === 0) initChat();
    } else {
      if(badge) badge.style.display = 'block';
    }
  });

  if(close) close.addEventListener('click', function(){
    box.classList.remove('open');
    if(badge) badge.style.display = 'block';
  });

  /* --- Init chat --- */
  function initChat(){
    body.innerHTML = '';
    if(userName){
      showTyping(function(){
        addBotMessage('Bienvenue ' + userName + ' ! ' + getWelcomeMessage());
        showQuickReplies();
        updateStatus();
      });
    } else {
      showTyping(function(){
        addBotMessage('Bonjour ! Comment vous appelez-vous ?');
      });
    }
  }

  /* --- Typing animation --- */
  function showTyping(cb){
    var t = document.createElement('div');
    t.className = 'wa-typing';
    t.innerHTML = '<span></span><span></span><span></span>';
    body.appendChild(t);
    body.scrollTop = body.scrollHeight;
    setTimeout(function(){ t.remove(); if(cb) cb(); }, 1200);
  }

  /* --- Add bot message --- */
  function addBotMessage(text){
    var m = document.createElement('div');
    m.className = 'wa-chat-msg bot';
    m.textContent = text;
    body.appendChild(m);
    body.scrollTop = body.scrollHeight;
  }

  /* --- Show quick reply buttons --- */
  function showQuickReplies(){
    var c = document.createElement('div');
    c.className = 'wa-quick-replies';
    quickReplies.forEach(function(text){
      var b = document.createElement('button');
      b.className = 'wa-quick-btn';
      b.textContent = text;
      b.addEventListener('click', function(){
        c.remove();
        if(text === 'Postuler maintenant'){
          window.location.href = 'carrieres.html#recrutement';
        } else {
          sendToWhatsApp(text);
        }
      });
      c.appendChild(b);
    });
    body.appendChild(c);
    body.scrollTop = body.scrollHeight;
  }

  /* --- Update online/offline status --- */
  function updateStatus(){
    var el = document.querySelector('.wa-chat-header-info p');
    if(!el) return;
    if(isOnline()){
      el.textContent = 'En ligne';
      el.style.opacity = '';
    } else {
      el.textContent = 'Hors ligne';
      el.style.opacity = '.55';
    }
  }

  /* --- Send message --- */
  function sendMessage(){
    var text = (input ? input.value : '').trim();
    if(!text) return;
    if(!userName){
      userName = text;
      try { localStorage.setItem('wa_user_name', userName); } catch(e){}
      var m = document.createElement('div');
      m.className = 'wa-chat-msg user';
      m.textContent = userName;
      body.appendChild(m);
      body.scrollTop = body.scrollHeight;
      input.value = '';
      showTyping(function(){
        addBotMessage('Bienvenue ' + userName + ' ! ' + getWelcomeMessage());
        showQuickReplies();
        updateStatus();
      });
      return;
    }
    sendToWhatsApp(text);
  }

  function sendToWhatsApp(text){
    var m = document.createElement('div');
    m.className = 'wa-chat-msg user';
    m.textContent = text;
    body.appendChild(m);
    body.scrollTop = body.scrollHeight;
    input.value = '';
    setTimeout(function(){
      window.open('https://wa.me/' + waNumber + '?text=' + encodeURIComponent(text), '_blank');
    }, 300);
  }

  if(send) send.addEventListener('click', sendMessage);
  if(input) input.addEventListener('keydown', function(e){
    if(e.key === 'Enter') sendMessage();
  });
});

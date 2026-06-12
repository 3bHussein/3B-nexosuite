document.addEventListener('DOMContentLoaded',()=>{
  document.querySelectorAll('[data-autosubmit]').forEach(el=>{
    el.addEventListener('change',()=>el.form && el.form.submit());
  });

  document.querySelectorAll('[data-qty-plus]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const input=document.querySelector(btn.dataset.qtyPlus);
      if(input){
        const max=parseInt(input.max||'999999',10);
        const next=Math.max(1,parseInt(input.value||'1',10)+1);
        input.value=Math.min(max,next);
      }
    });
  });

  document.querySelectorAll('[data-qty-minus]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const input=document.querySelector(btn.dataset.qtyMinus);
      if(input){
        input.value=Math.max(1,parseInt(input.value||'1',10)-1);
      }
    });
  });

  const categoryToggle=document.querySelector('[data-category-toggle]');
  const categoryPanel=document.querySelector('[data-category-panel]');
  if(categoryToggle && categoryPanel){
    categoryToggle.addEventListener('click',()=>{
      categoryPanel.classList.toggle('open');
    });
    document.addEventListener('click',(event)=>{
      if(!categoryPanel.contains(event.target) && !categoryToggle.contains(event.target)){
        categoryPanel.classList.remove('open');
      }
    });
  }

  const galleryMain=document.querySelector('[data-gallery-main]');
  document.querySelectorAll('[data-gallery-image]').forEach(button=>{
    button.addEventListener('click',()=>{
      if(!galleryMain) return;
      galleryMain.src=button.dataset.galleryImage;
      document.querySelectorAll('[data-gallery-image]').forEach(item=>item.classList.remove('active'));
      button.classList.add('active');
    });
  });


  const readingProgress=document.querySelector('[data-reading-progress]');
  const readingArticle=document.querySelector('[data-reading-article]');
  if(readingProgress && readingArticle){
    const updateReadingProgress=()=>{
      const articleTop=readingArticle.offsetTop;
      const articleHeight=Math.max(readingArticle.offsetHeight-window.innerHeight*.45,1);
      const current=window.scrollY-articleTop+140;
      const percent=Math.max(0,Math.min(100,(current/articleHeight)*100));
      readingProgress.style.width=percent.toFixed(2)+'%';
    };
    updateReadingProgress();
    window.addEventListener('scroll',updateReadingProgress,{passive:true});
    window.addEventListener('resize',updateReadingProgress);
  }

});

const mobileDrawer = document.querySelector('[data-mobile-commerce-drawer]');
const mobileDrawerBackdrop = document.querySelector('[data-mobile-drawer-backdrop]');
const mobileDrawerOpeners = document.querySelectorAll('[data-mobile-drawer-open]');
const mobileDrawerClosers = document.querySelectorAll('[data-mobile-drawer-close]');

function setMobileDrawer(open) {
  if (!mobileDrawer || !mobileDrawerBackdrop) return;
  mobileDrawer.classList.toggle('is-open', open);
  mobileDrawerBackdrop.classList.toggle('is-open', open);
  mobileDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
  document.body.classList.toggle('mobile-drawer-open', open);
}

mobileDrawerOpeners.forEach((button) => {
  button.addEventListener('click', () => setMobileDrawer(true));
});

mobileDrawerClosers.forEach((button) => {
  button.addEventListener('click', () => setMobileDrawer(false));
});

if (mobileDrawerBackdrop) {
  mobileDrawerBackdrop.addEventListener('click', () => setMobileDrawer(false));
}

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    setMobileDrawer(false);
  }
});


// Mobile drawer robust open/close fix
(function(){
  function ready(fn){
    if(document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function(){
    var drawer = document.querySelector('[data-mobile-commerce-drawer], .mobile-commerce-drawer');
    var backdrop = document.querySelector('[data-mobile-drawer-backdrop], .mobile-drawer-backdrop');
    if(!drawer){ return; }

    function setDrawer(open){
      if(!open && drawer.contains(document.activeElement)){ document.activeElement.blur(); }
      document.body.classList.toggle('mobile-drawer-open', !!open);
      drawer.classList.toggle('is-open', !!open);
      drawer.classList.toggle('drawer-open', !!open);
      drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
      if(backdrop){
        backdrop.classList.toggle('is-open', !!open);
        backdrop.classList.toggle('drawer-open', !!open);
      }
    }

    document.querySelectorAll('[data-mobile-drawer-open]').forEach(function(btn){
      if(btn.dataset.drawerOpenBound === '1') return;
      btn.dataset.drawerOpenBound = '1';
      btn.addEventListener('click', function(e){
        e.preventDefault();
        setDrawer(true);
      });
    });

    document.querySelectorAll('[data-mobile-drawer-close], .mobile-drawer-close-btn').forEach(function(btn){
      if(btn.dataset.drawerCloseBound === '1') return;
      btn.dataset.drawerCloseBound = '1';
      btn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        setDrawer(false);
      });
    });

    if(backdrop && backdrop.dataset.drawerBackdropBound !== '1'){
      backdrop.dataset.drawerBackdropBound = '1';
      backdrop.addEventListener('click', function(e){
        e.preventDefault();
        setDrawer(false);
      });
    }

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape') setDrawer(false);
    });

    drawer.querySelectorAll('a').forEach(function(link){
      if(link.dataset.drawerLinkBound === '1') return;
      link.dataset.drawerLinkBound = '1';
      link.addEventListener('click', function(){
        setDrawer(false);
      });
    });
  });
})();




// Mobile drawer aria-hidden focus safety
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    var drawer = document.querySelector('[data-mobile-commerce-drawer], .mobile-commerce-drawer');
    if(!drawer) return;
    document.querySelectorAll('[data-mobile-drawer-close], .mobile-drawer-close-btn').forEach(function(btn){
      btn.addEventListener('click', function(){
        if(drawer.contains(document.activeElement)){ document.activeElement.blur(); }
        drawer.setAttribute('aria-hidden','true');
        document.body.classList.remove('mobile-drawer-open');
        drawer.classList.remove('is-open','drawer-open','active');
        var backdrop = document.querySelector('[data-mobile-drawer-backdrop], .mobile-drawer-backdrop');
        if(backdrop){ backdrop.classList.remove('is-open','drawer-open','active'); }
      });
    });
    document.querySelectorAll('[data-mobile-drawer-open]').forEach(function(btn){
      btn.addEventListener('click', function(){
        drawer.setAttribute('aria-hidden','false');
      });
    });
  });
})();


// Close mobile drawer safely before currency navigation
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }
  ready(function(){
    function closeDrawerNow(){
      var drawer = document.querySelector('[data-mobile-commerce-drawer], .mobile-commerce-drawer');
      var backdrop = document.querySelector('[data-mobile-drawer-backdrop], .mobile-drawer-backdrop');
      if(document.activeElement && drawer && drawer.contains(document.activeElement)){
        document.activeElement.blur();
      }
      document.body.classList.remove('mobile-drawer-open');
      if(drawer){
        drawer.classList.remove('is-open','drawer-open','active');
        drawer.setAttribute('aria-hidden','true');
      }
      if(backdrop){
        backdrop.classList.remove('is-open','drawer-open','active');
      }
    }

    document.querySelectorAll('[data-currency-switch-link], .drawer-currency-button-v3, .drawer-currency-button-v2, .drawer-currency-pill').forEach(function(link){
      if(link.dataset.currencyCloseBound === '1') return;
      link.dataset.currencyCloseBound = '1';
      link.addEventListener('click', function(){
        closeDrawerNow();
      });
    });

    document.querySelectorAll('[data-mobile-drawer-close], .mobile-drawer-close-btn').forEach(function(btn){
      if(btn.dataset.closeFocusFixBound === '1') return;
      btn.dataset.closeFocusFixBound = '1';
      btn.addEventListener('click', function(){
        closeDrawerNow();
      });
    });
  });
})();


// RTL drawer final hard position fix
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }

  ready(function(){
    var drawer = document.querySelector('[data-mobile-commerce-drawer], .mobile-commerce-drawer');
    var backdrop = document.querySelector('[data-mobile-drawer-backdrop], .mobile-drawer-backdrop');
    if(!drawer) return;

    function isRTL(){
      return document.documentElement.getAttribute('dir') === 'rtl' || document.body.classList.contains('rtl-site');
    }

    function applyDrawerSide(){
      drawer.style.position = 'fixed';
      drawer.style.top = '0';
      drawer.style.bottom = '0';
      drawer.style.width = 'min(430px, 92vw)';
      drawer.style.maxWidth = '92vw';
      drawer.style.zIndex = '99990';
      drawer.style.overflowY = 'auto';
      drawer.style.overflowX = 'hidden';

      if(isRTL()){
        drawer.style.right = '0';
        drawer.style.left = 'auto';
      } else {
        drawer.style.left = '0';
        drawer.style.right = 'auto';
      }
    }

    function openDrawer(){
      applyDrawerSide();
      drawer.style.transform = 'translateX(0)';
      drawer.setAttribute('aria-hidden', 'false');
      drawer.classList.add('is-open', 'drawer-open');
      document.body.classList.add('mobile-drawer-open');
      if(backdrop){
        backdrop.classList.add('is-open', 'drawer-open');
      }
    }

    function closeDrawer(){
      if(drawer.contains(document.activeElement)){
        document.activeElement.blur();
      }
      applyDrawerSide();
      drawer.style.transform = isRTL() ? 'translateX(105%)' : 'translateX(-105%)';
      drawer.setAttribute('aria-hidden', 'true');
      drawer.classList.remove('is-open', 'drawer-open', 'active');
      document.body.classList.remove('mobile-drawer-open');
      if(backdrop){
        backdrop.classList.remove('is-open', 'drawer-open', 'active');
      }
    }

    applyDrawerSide();

    document.querySelectorAll('[data-mobile-drawer-open]').forEach(function(btn){
      if(btn.dataset.rtlDrawerOpenBound === '1') return;
      btn.dataset.rtlDrawerOpenBound = '1';
      btn.addEventListener('click', function(e){
        e.preventDefault();
        openDrawer();
      });
    });

    document.querySelectorAll('[data-mobile-drawer-close], .mobile-drawer-close-btn').forEach(function(btn){
      if(btn.dataset.rtlDrawerCloseBound === '1') return;
      btn.dataset.rtlDrawerCloseBound = '1';
      btn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        closeDrawer();
      });
    });

    if(backdrop && backdrop.dataset.rtlDrawerBackdropBound !== '1'){
      backdrop.dataset.rtlDrawerBackdropBound = '1';
      backdrop.addEventListener('click', function(e){
        e.preventDefault();
        closeDrawer();
      });
    }

    document.addEventListener('keydown', function(e){
      if(e.key === 'Escape') closeDrawer();
    });

    window.addEventListener('resize', applyDrawerSide);
  });
})();


// Mobile drawer full width after language switch fix
(function(){
  function ready(fn){ if(document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }

  ready(function(){
    var drawer = document.querySelector('[data-mobile-commerce-drawer], .mobile-commerce-drawer');
    if(!drawer) return;

    function isRTL(){
      return document.documentElement.getAttribute('dir') === 'rtl' || document.body.classList.contains('rtl-site');
    }

    function applyMobileDrawerGeometry(){
      if(window.innerWidth <= 767){
        drawer.style.position = 'fixed';
        drawer.style.top = '0';
        drawer.style.bottom = '0';
        drawer.style.width = '100vw';
        drawer.style.maxWidth = '100vw';
        drawer.style.minWidth = '100vw';
        drawer.style.overflowX = 'hidden';

        if(isRTL()){
          drawer.style.right = '0';
          drawer.style.left = 'auto';
          if(!document.body.classList.contains('mobile-drawer-open') && !drawer.classList.contains('is-open')){
            drawer.style.transform = 'translateX(100%)';
          }
        } else {
          drawer.style.left = '0';
          drawer.style.right = 'auto';
          if(!document.body.classList.contains('mobile-drawer-open') && !drawer.classList.contains('is-open')){
            drawer.style.transform = 'translateX(-100%)';
          }
        }
      }
    }

    function forceOpenGeometry(){
      applyMobileDrawerGeometry();
      if(window.innerWidth <= 767){
        drawer.style.transform = 'translateX(0)';
      }
    }

    document.querySelectorAll('[data-mobile-drawer-open]').forEach(function(btn){
      if(btn.dataset.fullWidthDrawerBound === '1') return;
      btn.dataset.fullWidthDrawerBound = '1';
      btn.addEventListener('click', function(){
        setTimeout(forceOpenGeometry, 0);
      });
    });

    window.addEventListener('resize', applyMobileDrawerGeometry);
    applyMobileDrawerGeometry();
  });
})();
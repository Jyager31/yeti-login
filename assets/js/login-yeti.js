/**
 * DevQ Login Yeti - Animated SVG character for WordPress login
 * Adapted from CodePen by Darin Senneff (https://codepen.io/tsouhaieb/pen/zVrxNe)
 * Uses GSAP 3 - no MorphSVG dependency
 */
(function () {
  'use strict';

  // WordPress form elements
  var email, password, showPasswordBtn, svgContainer;

  // SVG elements
  var eyeL, eyeR, nose, mouth, chin, face, eyebrow;
  var outerEarL, outerEarR, earHairL, earHairR, hair;
  var armL, armR, twoFingers;

  // State
  var eyesCovered = false;
  var blinkTween = null;
  var chinMin = 0.5;

  // Coordinates
  var svgCoords, emailCoords, screenCenter, emailScrollMax;
  var eyeLCoords, eyeRCoords, noseCoords, mouthCoords;

  function getAngle(x1, y1, x2, y2) {
    return Math.atan2(y1 - y2, x1 - x2);
  }

  function getPosition(el) {
    var rect = el.getBoundingClientRect();
    return {
      x: rect.left + window.pageXOffset,
      y: rect.top + window.pageYOffset
    };
  }

  function calculateFaceMove() {
    if (!email) return;

    var carPos = email.selectionEnd;
    if (carPos == null || carPos === 0) {
      carPos = email.value.length;
    }

    var div = document.createElement('div');
    var span = document.createElement('span');
    var copyStyle = getComputedStyle(email);

    [].forEach.call(copyStyle, function (prop) {
      div.style[prop] = copyStyle[prop];
    });
    div.style.position = 'absolute';
    div.style.top = '-9999px';
    div.style.left = '0px';
    div.style.visibility = 'hidden';
    document.body.appendChild(div);
    div.textContent = email.value.substr(0, carPos);
    span.textContent = email.value.substr(carPos) || '.';
    div.appendChild(span);

    // Recalculate positions fresh each time (handles scroll/resize)
    emailCoords = getPosition(email);
    svgCoords = getPosition(svgContainer);
    screenCenter = svgCoords.x + (svgContainer.offsetWidth / 2);
    eyeLCoords = { x: svgCoords.x + 84, y: svgCoords.y + 76 };
    eyeRCoords = { x: svgCoords.x + 113, y: svgCoords.y + 76 };
    noseCoords = { x: svgCoords.x + 97, y: svgCoords.y + 81 };
    mouthCoords = { x: svgCoords.x + 100, y: svgCoords.y + 100 };

    // Get text width up to caret as offset from input left edge
    var textWidth = span.offsetLeft;
    var targetX = emailCoords.x + Math.min(textWidth, emailScrollMax);
    var targetY = emailCoords.y + 25;
    var dFromC;
    var eyeLAngle, eyeRAngle, noseAngle, mouthAngle;

    dFromC = screenCenter - targetX;
    eyeLAngle = getAngle(eyeLCoords.x, eyeLCoords.y, targetX, targetY);
    eyeRAngle = getAngle(eyeRCoords.x, eyeRCoords.y, targetX, targetY);
    noseAngle = getAngle(noseCoords.x, noseCoords.y, targetX, targetY);
    mouthAngle = getAngle(mouthCoords.x, mouthCoords.y, targetX, targetY);

    var eyeLX = Math.cos(eyeLAngle) * 20;
    var eyeLY = Math.sin(eyeLAngle) * 10;
    var eyeRX = Math.cos(eyeRAngle) * 20;
    var eyeRY = Math.sin(eyeRAngle) * 10;
    var noseX = Math.cos(noseAngle) * 23;
    var noseY = Math.sin(noseAngle) * 10;
    var mouthX = Math.cos(mouthAngle) * 23;
    var mouthY = Math.sin(mouthAngle) * 10;
    var mouthR = Math.cos(mouthAngle) * 6;
    var chinX = mouthX * 0.8;
    var chinY = mouthY * 0.5;
    var chinS = 1 - ((dFromC * 0.15) / 100);
    if (chinS > 1) {
      chinS = 1 - (chinS - 1);
      if (chinS < chinMin) chinS = chinMin;
    }
    var faceX = mouthX * 0.3;
    var faceY = mouthY * 0.4;
    var faceSkew = Math.cos(mouthAngle) * 5;
    var eyebrowSkew = Math.cos(mouthAngle) * 25;
    var outerEarX = Math.cos(mouthAngle) * 4;
    var outerEarY = Math.cos(mouthAngle) * 5;
    var hairX = Math.cos(mouthAngle) * 6;

    gsap.to(eyeL, { duration: 1, x: -eyeLX, y: -eyeLY, ease: 'expo.out' });
    gsap.to(eyeR, { duration: 1, x: -eyeRX, y: -eyeRY, ease: 'expo.out' });
    gsap.to(nose, { duration: 1, x: -noseX, y: -noseY, rotation: mouthR, transformOrigin: 'center center', ease: 'expo.out' });
    gsap.to(mouth, { duration: 1, x: -mouthX, y: -mouthY, rotation: mouthR, transformOrigin: 'center center', ease: 'expo.out' });
    gsap.to(chin, { duration: 1, x: -chinX, y: -chinY, scaleY: chinS, ease: 'expo.out' });
    gsap.to(face, { duration: 1, x: -faceX, y: -faceY, skewX: -faceSkew, transformOrigin: 'center top', ease: 'expo.out' });
    gsap.to(eyebrow, { duration: 1, x: -faceX, y: -faceY, skewX: -eyebrowSkew, transformOrigin: 'center top', ease: 'expo.out' });
    gsap.to(outerEarL, { duration: 1, x: outerEarX, y: -outerEarY, ease: 'expo.out' });
    gsap.to(outerEarR, { duration: 1, x: outerEarX, y: outerEarY, ease: 'expo.out' });
    gsap.to(earHairL, { duration: 1, x: -outerEarX, y: -outerEarY, ease: 'expo.out' });
    gsap.to(earHairR, { duration: 1, x: -outerEarX, y: outerEarY, ease: 'expo.out' });
    gsap.to(hair, { duration: 1, x: hairX, scaleY: 1.2, transformOrigin: 'center bottom', ease: 'expo.out' });

    document.body.removeChild(div);
  }

  function resetFace() {
    gsap.to([eyeL, eyeR], { duration: 1, x: 0, y: 0, ease: 'expo.out' });
    gsap.to(nose, { duration: 1, x: 0, y: 0, scaleX: 1, scaleY: 1, rotation: 0, ease: 'expo.out' });
    gsap.to(mouth, { duration: 1, x: 0, y: 0, rotation: 0, ease: 'expo.out' });
    gsap.to(chin, { duration: 1, x: 0, y: 0, scaleY: 1, ease: 'expo.out' });
    gsap.to([face, eyebrow], { duration: 1, x: 0, y: 0, skewX: 0, ease: 'expo.out' });
    gsap.to([outerEarL, outerEarR, earHairL, earHairR, hair], { duration: 1, x: 0, y: 0, scaleY: 1, ease: 'expo.out' });
  }

  function coverEyes() {
    gsap.killTweensOf([armL, armR]);
    gsap.set([armL, armR], { visibility: 'visible' });
    gsap.to(armL, { duration: 0.45, x: -93, y: 10, rotation: 0, ease: 'quad.out' });
    gsap.to(armR, { duration: 0.45, x: -93, y: 10, rotation: 0, ease: 'quad.out', delay: 0.1 });
    eyesCovered = true;
  }

  function uncoverEyes() {
    gsap.killTweensOf([armL, armR]);
    gsap.to(armL, { duration: 1.35, y: 220, ease: 'quad.out' });
    gsap.to(armL, { duration: 1.35, rotation: 105, ease: 'quad.out', delay: 0.1 });
    gsap.to(armR, { duration: 1.35, y: 220, ease: 'quad.out' });
    gsap.to(armR, {
      duration: 1.35,
      rotation: -105,
      ease: 'quad.out',
      delay: 0.1,
      onComplete: function () {
        gsap.set([armL, armR], { visibility: 'hidden' });
      }
    });
    eyesCovered = false;
  }

  function spreadFingers() {
    gsap.to(twoFingers, { duration: 0.35, transformOrigin: 'bottom left', rotation: 30, x: -9, y: -2, ease: 'power2.inOut' });
  }

  function closeFingers() {
    gsap.to(twoFingers, { duration: 0.35, transformOrigin: 'bottom left', rotation: 0, x: 0, y: 0, ease: 'power2.inOut' });
  }

  function startBlinking(delay) {
    var d = delay ? Math.floor(Math.random() * delay) : 1;
    blinkTween = gsap.to([eyeL, eyeR], {
      duration: 0.1,
      delay: d,
      scaleY: 0,
      yoyo: true,
      repeat: 1,
      transformOrigin: 'center center',
      onComplete: function () {
        startBlinking(12);
      }
    });
  }

  // --- Event handlers ---

  function onEmailFocus() {
    calculateFaceMove();
  }

  function onEmailInput() {
    calculateFaceMove();
  }

  function onPasswordFocus() {
    resetFace();
    if (!eyesCovered) coverEyes();
  }

  function onBlur() {
    setTimeout(function () {
      var active = document.activeElement;
      // Stay covered if still in password-related elements
      if (password && (active === password || active === showPasswordBtn)) return;
      // Uncover eyes if they were covered
      if (eyesCovered) uncoverEyes();
      // Reset face if not in email field
      if (active !== email) resetFace();
    }, 150);
  }

  function onTogglePassword() {
    setTimeout(function () {
      if (password.type === 'text') {
        spreadFingers();
      } else {
        closeFingers();
      }
    }, 100);
  }

  // --- Init ---

  function init() {
    // WordPress login form elements
    email = document.querySelector('#user_login');
    password = document.querySelector('#user_pass');
    showPasswordBtn = document.querySelector('.wp-hide-pw');
    svgContainer = document.querySelector('.svgContainer');

    if (!email || !svgContainer) return;

    // SVG character elements
    eyeL = document.querySelector('.eyeL');
    eyeR = document.querySelector('.eyeR');
    nose = document.querySelector('.nose');
    mouth = document.querySelector('.mouth');
    chin = document.querySelector('.chin');
    face = document.querySelector('.face');
    eyebrow = document.querySelector('.eyebrow');
    outerEarL = document.querySelector('.earL .outerEar');
    outerEarR = document.querySelector('.earR .outerEar');
    earHairL = document.querySelector('.earL .earHair');
    earHairR = document.querySelector('.earR .earHair');
    hair = document.querySelector('.hair');
    armL = document.querySelector('.armL');
    armR = document.querySelector('.armR');
    twoFingers = document.querySelector('.twoFingers');

    // Calculate positions
    svgCoords = getPosition(svgContainer);
    emailCoords = getPosition(email);
    screenCenter = svgCoords.x + (svgContainer.offsetWidth / 2);
    eyeLCoords = { x: svgCoords.x + 84, y: svgCoords.y + 76 };
    eyeRCoords = { x: svgCoords.x + 113, y: svgCoords.y + 76 };
    noseCoords = { x: svgCoords.x + 97, y: svgCoords.y + 81 };
    mouthCoords = { x: svgCoords.x + 100, y: svgCoords.y + 100 };

    // Initial arm positions (hidden, down and rotated out)
    gsap.set(armL, { x: -93, y: 220, rotation: 105, transformOrigin: 'top left' });
    gsap.set(armR, { x: -93, y: 220, rotation: -105, transformOrigin: 'top right' });
    gsap.set(mouth, { transformOrigin: 'center center' });

    // Email/username events
    email.addEventListener('focus', onEmailFocus);
    email.addEventListener('blur', onBlur);
    email.addEventListener('input', onEmailInput);

    // Password events (only exists on login page)
    if (password) {
      password.addEventListener('focus', onPasswordFocus);
      password.addEventListener('blur', onBlur);
    }

    // WP show/hide password toggle
    if (showPasswordBtn) {
      showPasswordBtn.addEventListener('click', onTogglePassword);
      showPasswordBtn.addEventListener('focus', function () {
        if (!eyesCovered) coverEyes();
      });
      showPasswordBtn.addEventListener('blur', onBlur);
    }

    // Start blinking
    startBlinking(5);

    // Max scroll width for eye tracking boundary
    emailScrollMax = email.scrollWidth;

    // Detect autofill and show the password toggle button.
    // Browsers fill values without firing input events, so WP
    // doesn't know the field has content. Poll briefly on load.
    if (password && showPasswordBtn) {
      var autofillChecks = 0;
      var autofillTimer = setInterval(function () {
        autofillChecks++;
        if (password.value.length > 0) {
          showPasswordBtn.style.display = '';
          showPasswordBtn.style.visibility = 'visible';
          clearInterval(autofillTimer);
        }
        if (autofillChecks >= 20) {
          clearInterval(autofillTimer);
        }
      }, 250);
    }
  }

  document.addEventListener('DOMContentLoaded', init);
})();

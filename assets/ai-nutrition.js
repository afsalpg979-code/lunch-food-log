(() => {
  'use strict';
  const food = document.querySelector('input[name="food_item"]');
  const form = document.getElementById('foodForm');
  if (!food || !form || document.querySelector('.ai-nutrition-card')) return;

  const q = document.querySelector('input[name="quantity"]');
  const fields = {
    calories: document.querySelector('input[name="calories"]'),
    protein: document.querySelector('input[name="protein"]'),
    carbs: document.querySelector('input[name="carbs"]'),
    fat: document.querySelector('input[name="fat"]')
  };

  const card = document.createElement('div');
  card.className = 'ai-nutrition-card';
  card.innerHTML = `
    <div class="ai-title">🤖 AI Food Scanner</div>
    <p class="ai-help">Take a meal photo, upload one from your gallery, or describe the food. AI estimates the food, quantity and nutrition values.</p>
    <div class="ai-buttons">
      <button type="button" class="ai-btn" id="aiAnalyzeText">✨ Analyze Food</button>
      <label class="ai-btn ai-scan" for="aiCameraInput">📷 Open Camera</label>
      <label class="ai-btn ai-upload" for="aiGalleryInput">🖼️ Upload Photo</label>
      <input id="aiCameraInput" type="file" accept="image/*" capture="environment" hidden>
      <input id="aiGalleryInput" type="file" accept="image/*" hidden>
    </div>
    <div id="aiStatus" class="ai-status" aria-live="polite"></div>`;
  form.insertBefore(card, form.querySelector('.grid'));

  const status = document.getElementById('aiStatus');
  const camera = document.getElementById('aiCameraInput');
  const gallery = document.getElementById('aiGalleryInput');
  const analyze = document.getElementById('aiAnalyzeText');

  function setStatus(text, bad = false) {
    status.textContent = text;
    status.className = 'ai-status' + (bad ? ' bad' : '');
  }

  async function callAI(file) {
    const fd = new FormData();
    const text = food.value.trim();
    if (text) fd.append('food_text', text);
    if (q && q.value.trim()) fd.append('quantity', q.value.trim());
    if (file) fd.append('food_image', file);
    setStatus('⏳ Analyzing your meal photo…');
    analyze.disabled = true;
    try {
      const r = await fetch('ai_nutrition.php', { method:'POST', body:fd, credentials:'same-origin' });
      const data = await r.json();
      if (!r.ok || !data.ok) throw new Error(data.error || 'AI analysis failed.');
      const x = data.result || {};
      if (x.food_item) food.value = x.food_item;
      if (q && x.quantity) q.value = x.quantity;
      if (fields.calories) fields.calories.value = x.calories ?? '';
      if (fields.protein) fields.protein.value = x.protein ?? '';
      if (fields.carbs) fields.carbs.value = x.carbs ?? '';
      if (fields.fat) fields.fat.value = x.fat ?? '';
      const conf = Number(x.confidence || 0);
      setStatus(`✓ Detected: ${x.food_item || 'meal'} · ${x.calories || 0} kcal · ${x.protein || 0}g protein · confidence ${conf}%${x.notes ? ' · '+x.notes : ''}`);
    } catch (e) {
      setStatus('⚠️ ' + e.message, true);
    } finally {
      analyze.disabled = false;
    }
  }

  analyze.addEventListener('click', () => {
    if (!food.value.trim()) {
      setStatus('Enter food details first, then tap Analyze Food.', true);
      food.focus();
      return;
    }
    callAI(null);
  });
  camera.addEventListener('change', () => { if (camera.files && camera.files[0]) callAI(camera.files[0]); });
  gallery.addEventListener('change', () => { if (gallery.files && gallery.files[0]) callAI(gallery.files[0]); });
})();

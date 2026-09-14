(() => {
  'use strict';

  // Small offline nutrition dictionary for common foods. Values are approximate
  // per typical serving and are intended for logging convenience, not medical use.
  const foods = [
    { keys:['idli'], name:'Idli', kcal:58, protein:2, carbs:12, fat:0.3, unit:1 },
    { keys:['dosa'], name:'Dosa', kcal:168, protein:4, carbs:28, fat:5, unit:1 },
    { keys:['chapati','roti'], name:'Chapati', kcal:120, protein:3.5, carbs:18, fat:3, unit:1 },
    { keys:['rice','cooked rice','white rice'], name:'Cooked Rice', kcal:205, protein:4.3, carbs:45, fat:0.4, unit:1 },
    { keys:['fish curry','fish'], name:'Fish', kcal:180, protein:25, carbs:3, fat:8, unit:1 },
    { keys:['chicken','chicken curry'], name:'Chicken', kcal:240, protein:27, carbs:5, fat:12, unit:1 },
    { keys:['egg','eggs','boiled egg'], name:'Egg', kcal:78, protein:6.3, carbs:0.6, fat:5.3, unit:1 },
    { keys:['banana'], name:'Banana', kcal:105, protein:1.3, carbs:27, fat:0.4, unit:1 },
    { keys:['guava'], name:'Guava', kcal:68, protein:2.6, carbs:14, fat:1, unit:1 },
    { keys:['milk'], name:'Milk', kcal:122, protein:8, carbs:12, fat:5, unit:1 },
    { keys:['curd','yogurt'], name:'Curd', kcal:100, protein:5, carbs:7, fat:5, unit:1 },
    { keys:['dal','lentil','parippu'], name:'Dal', kcal:170, protein:9, carbs:27, fat:3, unit:1 },
    { keys:['vegetable','vegetables','veg curry','sabzi'], name:'Vegetables', kcal:100, protein:4, carbs:15, fat:3, unit:1 },
    { keys:['peanut','peanuts'], name:'Peanuts', kcal:170, protein:7, carbs:6, fat:14, unit:1 },
    { keys:['almond','almonds'], name:'Almonds', kcal:164, protein:6, carbs:6, fat:14, unit:1 },
    { keys:['apple'], name:'Apple', kcal:95, protein:0.5, carbs:25, fat:0.3, unit:1 },
    { keys:['orange'], name:'Orange', kcal:62, protein:1.2, carbs:15, fat:0.2, unit:1 },
    { keys:['oats','oatmeal'], name:'Oats', kcal:150, protein:5, carbs:27, fat:3, unit:1 },
    { keys:['bread'], name:'Bread', kcal:80, protein:3, carbs:14, fat:1, unit:1 },
    { keys:['potato'], name:'Potato', kcal:130, protein:3, carbs:30, fat:0.2, unit:1 }
  ];

  const $ = id => document.getElementById(id);
  const food = document.querySelector('input[name="food_item"]');
  if (!food) return;
  const fields = { calories:$('autoCalories'), protein:$('autoProtein'), carbs:$('autoCarbs'), fat:$('autoFat') };
  const quantity = document.querySelector('input[name="quantity"]');
  const status = $('nutritionDetectStatus');
  const button = $('autoNutritionBtn');
  let lastAuto = { calories:'', protein:'', carbs:'', fat:'' };

  function numberFromText(text) {
    const m = String(text || '').match(/(?:^|\s|x)(\d+(?:\.\d+)?)(?=\s|x|$)/i);
    return m ? Math.max(0.1, parseFloat(m[1])) : 1;
  }

  function detect() {
    const text = `${food.value} ${quantity?.value || ''}`.toLowerCase().trim();
    if (!text) return null;
    const matched = [];
    for (const item of foods) {
      if (item.keys.some(k => text.includes(k))) matched.push(item);
    }
    if (!matched.length) return null;

    // If the entry names one food and includes a number, treat it as that many servings.
    // For mixed entries, each detected food contributes one typical serving unless
    // a clear quantity is supplied in the quantity field.
    const multiplier = numberFromText(quantity?.value || '') || (matched.length === 1 ? numberFromText(food.value) : 1);
    const factor = (quantity?.value || '').trim() ? multiplier : 1;
    const totals = matched.reduce((a, item) => {
      a.calories += item.kcal * factor;
      a.protein += item.protein * factor;
      a.carbs += item.carbs * factor;
      a.fat += item.fat * factor;
      return a;
    }, { calories:0, protein:0, carbs:0, fat:0 });
    return { ...totals, names:matched.map(x => x.name) };
  }

  function setIfAuto(field, value) {
    if (!field) return;
    const current = field.value.trim();
    const previous = String(lastAuto[field.name === 'calories' ? 'calories' : field.name] ?? '');
    if (!current || current === previous) field.value = value;
  }

  function run() {
    const result = detect();
    if (!result) {
      if (status) status.textContent = 'Nutrition not found — you can enter values manually.';
      return;
    }
    const vals = {
      calories: result.calories.toFixed(0),
      protein: result.protein.toFixed(1),
      carbs: result.carbs.toFixed(1),
      fat: result.fat.toFixed(1)
    };
    if (fields.calories) fields.calories.value = vals.calories;
    if (fields.protein) fields.protein.value = vals.protein;
    if (fields.carbs) fields.carbs.value = vals.carbs;
    if (fields.fat) fields.fat.value = vals.fat;
    lastAuto = vals;
    if (status) status.textContent = `✓ Detected: ${result.names.join(' + ')} · approximate nutrition added`;
  }

  food.addEventListener('blur', run);
  quantity?.addEventListener('change', run);
  quantity?.addEventListener('blur', run);
  button?.addEventListener('click', run);
})();

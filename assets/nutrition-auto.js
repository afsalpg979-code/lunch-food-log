(() => {
  'use strict';

  // Offline nutrition helper. Values are approximate typical servings.
  const foods = [
    {keys:['idli'],name:'Idli',kcal:58,protein:2,carbs:12,fat:.3},
    {keys:['dosa'],name:'Dosa',kcal:168,protein:4,carbs:28,fat:5},
    {keys:['chapati','roti'],name:'Chapati',kcal:120,protein:3.5,carbs:18,fat:3},
    {keys:['rice','cooked rice','white rice'],name:'Cooked Rice',kcal:205,protein:4.3,carbs:45,fat:.4},
    {keys:['fish curry','fish'],name:'Fish',kcal:180,protein:25,carbs:3,fat:8},
    {keys:['chicken','chicken curry'],name:'Chicken',kcal:240,protein:27,carbs:5,fat:12},
    {keys:['egg','eggs','boiled egg'],name:'Egg',kcal:78,protein:6.3,carbs:.6,fat:5.3},
    {keys:['banana'],name:'Banana',kcal:105,protein:1.3,carbs:27,fat:.4},
    {keys:['guava'],name:'Guava',kcal:68,protein:2.6,carbs:14,fat:1},
    {keys:['milk'],name:'Milk',kcal:122,protein:8,carbs:12,fat:5},
    {keys:['curd','yogurt'],name:'Curd',kcal:100,protein:5,carbs:7,fat:5},
    {keys:['dal','lentil','parippu'],name:'Dal',kcal:170,protein:9,carbs:27,fat:3},
    {keys:['vegetable','vegetables','veg curry','sabzi'],name:'Vegetables',kcal:100,protein:4,carbs:15,fat:3},
    {keys:['peanut','peanuts'],name:'Peanuts',kcal:170,protein:7,carbs:6,fat:14},
    {keys:['almond','almonds'],name:'Almonds',kcal:164,protein:6,carbs:6,fat:14},
    {keys:['apple'],name:'Apple',kcal:95,protein:.5,carbs:25,fat:.3},
    {keys:['orange'],name:'Orange',kcal:62,protein:1.2,carbs:15,fat:.2},
    {keys:['oats','oatmeal'],name:'Oats',kcal:150,protein:5,carbs:27,fat:3},
    {keys:['bread'],name:'Bread',kcal:80,protein:3,carbs:14,fat:1},
    {keys:['potato'],name:'Potato',kcal:130,protein:3,carbs:30,fat:.2}
  ];

  const food=document.querySelector('input[name="food_item"]');
  if(!food)return;
  const quantity=document.querySelector('input[name="quantity"]');
  const fields={
    calories:document.querySelector('input[name="calories"]'),
    protein:document.querySelector('input[name="protein"]'),
    carbs:document.querySelector('input[name="carbs"]'),
    fat:document.querySelector('input[name="fat"]')
  };

  // Add a clear Auto Detect control without requiring changes to index.php.
  const form=food.closest('form');
  let status=document.getElementById('nutritionDetectStatus');
  let button=document.getElementById('autoNutritionBtn');
  if(form&&!button){
    const box=document.createElement('div');
    box.style.cssText='grid-column:1/-1;margin-top:-2px';
    button=document.createElement('button');button.type='button';button.id='autoNutritionBtn';button.textContent='✨ Auto Detect Nutrition';
    button.style.cssText='width:100%;border:1px solid #bbf7d0;background:#f0fdf4;color:#166534;border-radius:12px;padding:11px;font-weight:800;cursor:pointer';
    status=document.createElement('div');status.id='nutritionDetectStatus';status.style.cssText='font-size:12px;color:#64748b;margin-top:7px';
    box.append(button,status);
    const nutritionField=fields.calories?.closest('div');
    (nutritionField?.parentElement||form).appendChild(box);
  }

  function numberFromText(text){
    const m=String(text||'').match(/(?:^|\s|x)(\d+(?:\.\d+)?)(?=\s|x|$)/i);
    return m?Math.max(.1,parseFloat(m[1])):1;
  }

  function detect(){
    const foodText=food.value.toLowerCase().trim();
    if(!foodText)return null;
    const matches=foods.filter(item=>item.keys.some(k=>foodText.includes(k)));
    if(!matches.length)return null;
    const q=(quantity?.value||'').trim();
    const factor=q?numberFromText(q):(matches.length===1?numberFromText(food.value):1);
    return matches.reduce((a,item)=>({
      calories:a.calories+item.kcal*factor,
      protein:a.protein+item.protein*factor,
      carbs:a.carbs+item.carbs*factor,
      fat:a.fat+item.fat*factor
    }),{calories:0,protein:0,carbs:0,fat:0,names:matches.map(x=>x.name)});
  }

  function run(){
    const r=detect();
    if(!r){if(status)status.textContent='Nutrition not found. You can enter the values manually.';return;}
    fields.calories.value=r.calories.toFixed(0);
    fields.protein.value=r.protein.toFixed(1);
    fields.carbs.value=r.carbs.toFixed(1);
    fields.fat.value=r.fat.toFixed(1);
    if(status)status.textContent=`✓ Detected ${r.names.join(' + ')} — approximate values added.`;
  }

  food.addEventListener('blur',run);
  quantity?.addEventListener('blur',run);
  quantity?.addEventListener('change',run);
  button?.addEventListener('click',run);
})();

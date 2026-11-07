export const defaultConfig = Object.freeze({
 version:1,
 theme:{wallColor:'#e7dfcd',floorColor:'#786349',ceilingColor:'#ece5d6',ambientIntensity:.5,hemisphereIntensity:1.6,bounceIntensity:5,spotlightColor:'#ffe4b2',toneMappingExposure:1.05},
 bench:{visible:false,x:0,z:.31},
 artworks:[
  {id:'boat',my:'အင်းလေးလှေ',en:'Inn Lay Boat',orientation:'landscape',widthInches:16.5,heightInches:12.5,priceMmk:250000,image:'boat.png',crop:[52,123,932,752],wall:'back',slot:-.76,height:1.5,scale:1,lightIntensity:8},
  {id:'cart',my:'ဖုန်လုံးလှည်း',en:'New Cart',orientation:'portrait',widthInches:12.5,heightInches:16.5,priceMmk:250000,image:'cart.png',crop:[117,32,794,992],wall:'left',slot:.7,height:1.5,scale:1,lightIntensity:8},
  {id:'karaweik',my:'ကရဝိတ်',en:'Karaweik',orientation:'landscape',widthInches:16.5,heightInches:12.5,priceMmk:250000,image:'karaweik.png',crop:[29,118,969,771],wall:'back',slot:.76,height:1.5,scale:1,lightIntensity:8},
  {id:'music',my:'ဆိုင်းဝိုင်း',en:'Sine Wine',orientation:'landscape',widthInches:16.5,heightInches:12.5,priceMmk:250000,image:'music.png',crop:[26,124,974,754],wall:'left',slot:-.8,height:1.5,scale:1,lightIntensity:8},
  {id:'lawka',my:'လောကနတ်',en:'Lawka Nat',orientation:'portrait',widthInches:12.5,heightInches:16.5,priceMmk:250000,image:'lawka.png',crop:[143,44,740,948],wall:'right',slot:-.8,height:1.5,scale:1,lightIntensity:8},
  {id:'buddha',my:'ကြာခံဘုရား',en:'Lotus Buddha',orientation:'portrait',widthInches:12.5,heightInches:16.5,priceMmk:250000,image:'buddha.png',crop:[152,58,720,934],wall:'right',slot:.7,height:1.5,scale:1,lightIntensity:8},
 ]
});
export const dimensions=a=>`${Number(a.widthInches).toFixed(1)}″ × ${Number(a.heightInches).toFixed(1)}″`;
export const priceText=(a,lang)=>lang==='my'?`${new Intl.NumberFormat('my-MM').format(a.priceMmk)} ကျပ်`:`MMK ${new Intl.NumberFormat('en-US').format(a.priceMmk)}`;
// Artworks without a room belong to the brass room (configs saved before the silver room existed).
export const roomOf=a=>a.room==='silver'?'silver':'brass';

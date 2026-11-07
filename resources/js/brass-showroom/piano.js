const TEMPO=62;
const BEAT_SECONDS=60/TEMPO;
const ROMANTIC_MELODY=[76,79,81,83,81,79,76,74,72,76,79,81,79,76,74,72,74,77,81,79,77,76,74,72,71,74,79,77,76,74,72,76];
const ROMANTIC_CHORDS=[[60,64,67,72],[59,62,67,71],[57,60,64,69],[55,59,64,67],[53,57,60,65],[52,55,60,64],[50,53,57,60],[55,59,62,67]];

const frequency=midi=>440*2**((midi-69)/12);

export function createPianoAmbience(onStateChange=()=>{}){
 let context=null,master=null,dry=null,delay=null,feedback=null,timer=null,nextBeat=0,step=0,playing=false;

 function buildAudioGraph(){
  const AudioContext=window.AudioContext||window.webkitAudioContext;
  if(!AudioContext)return false;
  context=new AudioContext();master=context.createGain();dry=context.createBiquadFilter();delay=context.createDelay(.8);feedback=context.createGain();
  master.gain.value=.25;dry.type='lowpass';dry.frequency.value=4800;dry.Q.value=.35;delay.delayTime.value=.31;feedback.gain.value=.2;
  dry.connect(master);dry.connect(delay);delay.connect(feedback);feedback.connect(delay);delay.connect(master);master.connect(context.destination);
  return true;
 }

 function pianoNote(midi,when,duration,level){
  const envelope=context.createGain(),tone=context.createBiquadFilter();tone.type='lowpass';tone.frequency.setValueAtTime(4200,when);tone.frequency.exponentialRampToValueAtTime(1150,when+Math.min(duration,2.8));tone.Q.value=.55;
  envelope.gain.setValueAtTime(.0001,when);envelope.gain.exponentialRampToValueAtTime(level,when+.012);envelope.gain.exponentialRampToValueAtTime(Math.max(.0001,level*.3),when+.42);envelope.gain.exponentialRampToValueAtTime(.0001,when+duration);
  envelope.connect(tone);tone.connect(dry);
  [[1,1],[2,.16],[3,.045]].forEach(([multiple,amount])=>{const oscillator=context.createOscillator(),partial=context.createGain();oscillator.type=multiple===1?'triangle':'sine';oscillator.frequency.setValueAtTime(frequency(midi)*multiple,when);partial.gain.value=amount;oscillator.connect(partial);partial.connect(envelope);oscillator.start(when);oscillator.stop(when+duration+.06);});
 }

 function scheduleBeat(when,index){
  pianoNote(ROMANTIC_MELODY[index],when,2.5,.064);
  const chord=ROMANTIC_CHORDS[Math.floor(index/4)%ROMANTIC_CHORDS.length],position=index%4;
  pianoNote(chord[position],when+.035,2.9,.026);
  if(position===0){pianoNote(chord[0]-12,when,3.7,.034);pianoNote(chord[2],when+.09,3.35,.018);}
 }

 function scheduler(){
  if(!context||!playing)return;
  while(nextBeat<context.currentTime+.45){scheduleBeat(nextBeat,step);nextBeat+=BEAT_SECONDS;step=(step+1)%ROMANTIC_MELODY.length;}
 }

 async function start(){
  if(!context&&!buildAudioGraph()){onStateChange(false);return false;}
  await context.resume();playing=true;nextBeat=Math.max(context.currentTime+.06,nextBeat);if(!timer)timer=window.setInterval(scheduler,100);scheduler();onStateChange(true);return true;
 }
 async function pause(){if(!context)return;playing=false;await context.suspend();onStateChange(false);}
 async function toggle(){if(playing){await pause();return false;}return start();}
 function handleVisibility(){if(!context)return;if(document.hidden&&playing)context.suspend();else if(!document.hidden&&playing)context.resume();}
 document.addEventListener('visibilitychange',handleVisibility);
 function destroy(){playing=false;if(timer)window.clearInterval(timer);timer=null;document.removeEventListener('visibilitychange',handleVisibility);context?.close();context=null;}
 return {start,pause,toggle,destroy,get playing(){return playing;}};
}

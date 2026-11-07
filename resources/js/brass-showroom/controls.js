import * as T from 'three';
export function attachControls(room,onSelect){
 const {camera,renderer}=room;const canvas=renderer.domElement;
 camera.rotation.reorder('YXZ');let yaw=camera.rotation.y,pitch=camera.rotation.x;
 let enabled=false;const keys=new Set();let axis={x:0,y:0};let drag=null;
 const ray=new T.Raycaster();const point=new T.Vector2();
 const down=(e)=>{if(!enabled||e.button!==0)return;canvas.setPointerCapture(e.pointerId);drag={id:e.pointerId,x:e.clientX,y:e.clientY,startX:e.clientX,startY:e.clientY,moved:false};};
 const move=(e)=>{if(!enabled||!drag||drag.id!==e.pointerId)return;const dx=e.clientX-drag.x,dy=e.clientY-drag.y;drag.moved ||= Math.hypot(e.clientX-drag.startX,e.clientY-drag.startY)>6;yaw-=dx*.003;pitch=T.MathUtils.clamp(pitch-dy*.003,-1.05,.95);camera.rotation.set(pitch,yaw,0);drag.x=e.clientX;drag.y=e.clientY;};
 const up=(e)=>{if(!drag||drag.id!==e.pointerId)return;const click=!drag.moved;drag=null;if(canvas.hasPointerCapture(e.pointerId))canvas.releasePointerCapture(e.pointerId);if(click&&enabled){const r=canvas.getBoundingClientRect();point.set((e.clientX-r.left)/r.width*2-1,-(e.clientY-r.top)/r.height*2+1);ray.setFromCamera(point,camera);const hit=ray.intersectObjects(room.rayTargets,false)[0];if(hit)onSelect(hit.object.userData.artworkIndex);}};
 const cancel=()=>{drag=null;};const clear=()=>{keys.clear();axis={x:0,y:0};drag=null;};
 const keydown=(e)=>{if(!enabled||e.target instanceof HTMLInputElement||e.target instanceof HTMLTextAreaElement||e.ctrlKey||e.metaKey||e.altKey)return;if(['KeyW','KeyA','KeyS','KeyD','ArrowUp','ArrowDown','ArrowLeft','ArrowRight','KeyQ','KeyE'].includes(e.code)){e.preventDefault();keys.add(e.code);}};
 const keyup=(e)=>keys.delete(e.code);
 canvas.addEventListener('pointerdown',down);canvas.addEventListener('pointermove',move);canvas.addEventListener('pointerup',up);canvas.addEventListener('pointercancel',cancel);canvas.addEventListener('lostpointercapture',cancel);
 window.addEventListener('keydown',keydown);window.addEventListener('keyup',keyup);window.addEventListener('blur',clear);document.addEventListener('visibilitychange',clear);
 room.setTick((dt)=>{if(!enabled)return;let x=axis.x+Number(keys.has('KeyD')||keys.has('ArrowRight'))-Number(keys.has('KeyA')||keys.has('ArrowLeft'));let forward=-axis.y+Number(keys.has('KeyW')||keys.has('ArrowUp'))-Number(keys.has('KeyS')||keys.has('ArrowDown'));const length=Math.hypot(x,forward);if(length>1){x/=length;forward/=length;}
 yaw+=(Number(keys.has('KeyQ'))-Number(keys.has('KeyE')))*dt*1.2;camera.rotation.set(pitch,yaw,0);
 const speed=(room.walkSpeed?.()??1.15)*dt;const nx=camera.position.x+(Math.cos(yaw)*x-Math.sin(yaw)*forward)*speed;const nz=camera.position.z+(-Math.sin(yaw)*x-Math.cos(yaw)*forward)*speed;
 if(room.canWalk(nx,camera.position.z))camera.position.x=nx;if(room.canWalk(camera.position.x,nz))camera.position.z=nz;
 });
 return {setEnabled(value){enabled=value;clear();},setAxis(x,y){axis={x,y};},reset(kind='brass'){clear();const pose=typeof kind==='object'?kind:room.entrancePose(kind);camera.position.set(...pose.position);camera.lookAt(...pose.lookAt);yaw=camera.rotation.y;pitch=camera.rotation.x;},dispose(){clear();canvas.removeEventListener('pointerdown',down);canvas.removeEventListener('pointermove',move);canvas.removeEventListener('pointerup',up);canvas.removeEventListener('pointercancel',cancel);canvas.removeEventListener('lostpointercapture',cancel);window.removeEventListener('keydown',keydown);window.removeEventListener('keyup',keyup);window.removeEventListener('blur',clear);document.removeEventListener('visibilitychange',clear);}};
}

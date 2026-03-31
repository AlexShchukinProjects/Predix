@extends('layout.main')

@section('content')
<style>
  @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Unbounded:wght@600;900&display=swap');
  :root {
    --bg:#0a0d14;--surface:#111827;--border:#2a3a55;
    --text:#e2e8f0;--dim:#475569;--muted:#94a3b8;
    --GA:#a8ff3e;--GB:#ffd60a;--GC:#ff9040;--GD:#d63af9;
    --FK:#ff4d4d;--CM:#00e5ff;
  }
  .schema-wrap * { box-sizing:border-box; margin:0; padding:0; }
  .schema-wrap {
    background:var(--bg);
    color:var(--text);
    font-family:'JetBrains Mono',monospace;
    min-height:calc(100vh - 70px);
    margin: -16px -16px 0;
  }
  .schema-wrap .hdr { padding:22px 36px 0; border-bottom:1px solid var(--border); }
  .schema-wrap .hdr-title { font-family:'Unbounded',sans-serif;font-size:20px;font-weight:900;color:var(--CM);text-transform:uppercase;letter-spacing:-.5px; }
  .schema-wrap .hdr-sub { font-size:10px;color:var(--dim);padding-bottom:14px;margin-top:4px; }
  .schema-wrap .tabs { display:flex;padding:0 36px;background:var(--surface);border-bottom:1px solid var(--border); }
  .schema-wrap .tab { padding:13px 20px;font-size:10px;font-weight:700;color:var(--dim);cursor:pointer;border-bottom:2px solid transparent;transition:.2s;letter-spacing:.8px;text-transform:uppercase; }
  .schema-wrap .tab:hover { color:var(--muted); }
  .schema-wrap .tab.active { color:var(--CM);border-bottom-color:var(--CM); }
  .schema-wrap .tc { display:none; }
  .schema-wrap .tc.active { display:block; }
  .schema-wrap .erd-wrap { padding:20px 36px;overflow:auto; }
  .schema-wrap .legend { display:flex;flex-wrap:wrap;gap:14px;margin-bottom:16px;align-items:center;font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:.4px; }
  .schema-wrap .li { display:flex;align-items:center;gap:6px; }
  .schema-wrap .ld { width:10px;height:10px;border-radius:2px;flex-shrink:0; }
  /* mapping tab */
  .schema-wrap .mw { padding:20px 36px; }
  .schema-wrap .md { font-size:12px;color:var(--muted);line-height:1.8;max-width:720px;margin-bottom:20px; }
  .schema-wrap .mg { display:grid;grid-template-columns:1fr 1fr;gap:18px;max-width:1080px; }
  .schema-wrap .ms { background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden; }
  .schema-wrap .msh { display:flex;align-items:center;gap:9px;padding:11px 16px;background:#1a2235;border-bottom:1px solid var(--border); }
  .schema-wrap .msd { width:9px;height:9px;border-radius:50%;flex-shrink:0; }
  .schema-wrap .mst { font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase; }
  .schema-wrap .msb { margin-left:auto;font-size:8px;padding:2px 7px;border-radius:4px;font-weight:700;text-transform:uppercase; }
  .schema-wrap .mr { display:grid;grid-template-columns:1fr 16px 1fr;align-items:center;padding:8px 16px;border-bottom:1px solid rgba(42,58,85,.35);gap:8px; }
  .schema-wrap .mr:last-child { border-bottom:none; }
  .schema-wrap .mr:hover { background:rgba(255,255,255,.02); }
  .schema-wrap .cf { font-size:10px;font-weight:600; }
  .schema-wrap .cs { font-size:10px;color:var(--muted); }
  .schema-wrap .ca { text-align:center;font-size:11px;color:var(--dim); }
  .schema-wrap .jn { padding:6px 16px;font-size:9px;color:var(--dim);border-bottom:1px solid rgba(42,58,85,.35);line-height:1.6; }
</style>

<div class="schema-wrap">
  <div class="hdr">
    <div class="hdr-title">Master Data — Database Schema</div>
    <div class="hdr-sub">Predictive Maintenance · GAES Data · Reliability Module</div>
  </div>
  <div class="tabs">
    <div class="tab active" onclick="schemaSw('erd')">ERD — All Tables</div>
    <div class="tab" onclick="schemaSw('map')">work_cards_master — Mapping</div>
    <div class="tab" onclick="schemaSw('flow')">Data Flow</div>
  </div>

  <!-- TAB 1 ERD -->
  <div id="stc-erd" class="tc active">
    <div class="erd-wrap">
      <div class="legend">
        <div class="li"><div class="ld" style="background:var(--GA)"></div>JOIN A → aircrafts</div>
        <div class="li"><div class="ld" style="background:var(--GB)"></div>JOIN B → projects</div>
        <div class="li"><div class="ld" style="background:var(--GC)"></div>JOIN C → eef_registry</div>
        <div class="li"><div class="ld" style="background:var(--GD)"></div>JOIN D → work_card_materials</div>
        <div class="li" style="margin-left:6px;border:1px solid #334155;padding:2px 8px;border-radius:4px;color:#ffd60a">▼ COMPUTED — computed fields (not in DB)</div>
        <div class="li"><div style="width:26px;height:0;border-top:2px dashed #ffd60a"></div>returned value</div>
      </div>
      <svg id="schema-erd-svg"></svg>
    </div>
  </div>

  <!-- TAB 2 MAPPING -->
  <div id="stc-map" class="tc">
    <div class="mw">
      <div class="md">
        <span style="color:var(--CM);font-weight:700">work_cards_master</span> stores 18 fields from CSV (WINGS).
        On every request the row is <em>enriched</em> from 4 tables via logical JOINs — the result is NOT stored in DB.
      </div>
      <div class="mg">
        <div class="ms">
          <div class="msh"><div class="msd" style="background:var(--CM)"></div><div class="mst" style="color:var(--CM)">Stored fields (CSV → work_cards_master)</div><div class="msb" style="background:rgba(0,229,255,.1);color:var(--CM);border:1px solid rgba(0,229,255,.2)">WINGS WO CSV</div></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">project</span><span class="ca">←</span><span class="cs">PROJECT#</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">project_type</span><span class="ca">←</span><span class="cs">PROJECT TYPE</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">aircraft_type</span><span class="ca">←</span><span class="cs">AIRCRAFT TYPE</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">tail_number</span><span class="ca">←</span><span class="cs">TAIL NUMBER</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">wo_station</span><span class="ca">←</span><span class="cs">WO STATION</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">work_order</span><span class="ca">←</span><span class="cs">WORK ORDER#</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">item</span><span class="ca">←</span><span class="cs">ITEM#</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">src_order</span><span class="ca">←</span><span class="cs">SRC ORDER#</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">src_item</span><span class="ca">←</span><span class="cs">SRC ITEM#</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">src_cust_card</span><span class="ca">←</span><span class="cs">SRC. CUST. CARD</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">description</span><span class="ca">←</span><span class="cs">DESCRIPTION</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">corrective_action</span><span class="ca">←</span><span class="cs">CORRECTIVE ACTION</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">ata</span><span class="ca">←</span><span class="cs">ATA</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">cust_card</span><span class="ca">←</span><span class="cs">CUST. CARD</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">order_type</span><span class="ca">←</span><span class="cs">ORDER TYPE</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">avg_time</span><span class="ca">←</span><span class="cs">AVG TIME</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">act_time</span><span class="ca">←</span><span class="cs">ACT. TIME</span></div>
          <div class="mr"><span class="cf" style="color:var(--CM)">aircraft_location</span><span class="ca">←</span><span class="cs">AIRCRAFT LOCATION</span></div>
        </div>
        <div style="display:flex;flex-direction:column;gap:14px">
          <div class="ms">
            <div class="msh"><div class="msd" style="background:var(--GC)"></div><div class="mst" style="color:var(--GC)">JOIN C → eef_registry</div><div class="msb" style="background:rgba(255,144,64,.1);color:var(--GC);border:1px solid rgba(255,144,64,.2)">EEF Excel</div></div>
            <div class="jn">Step 1: <code style="color:var(--GC)">project</code> → filter pool by <code style="color:var(--GC)">project_no</code><br>Step 2: key = <code style="color:var(--GC)">{work_order}-{item×4digit}</code> = <code style="color:var(--GC)">nrc_number</code></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">EEF#</span><span class="ca">←</span><span class="cs" style="color:var(--GC)">eef_number</span></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">DATA SOURCE</span><span class="ca">←</span><span class="cs" style="color:var(--GC)">inspection_source_task</span></div>
          </div>
          <div class="ms">
            <div class="msh"><div class="msd" style="background:var(--GA)"></div><div class="mst" style="color:var(--GA)">JOIN A → aircrafts</div><div class="msb" style="background:rgba(168,255,62,.1);color:var(--GA);border:1px solid rgba(168,255,62,.2)">WINGS PR_0030</div></div>
            <div class="jn">JOIN: <code style="color:var(--GA)">TRIM(tail_number)</code> = <code style="color:var(--GA)">TRIM(aircrafts.tail_number)</code></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">MSN</span><span class="ca">←</span><span class="cs" style="color:var(--GA)">serial_number</span></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">AGE</span><span class="ca">←</span><span class="cs" style="color:var(--GA)">manufactured</span></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">EQUIP*</span><span class="ca">←</span><span class="cs" style="color:var(--GA)">engine_type (fallback)</span></div>
          </div>
          <div class="ms">
            <div class="msh"><div class="msd" style="background:var(--GB)"></div><div class="mst" style="color:var(--GB)">JOIN B → projects</div><div class="msb" style="background:rgba(255,214,10,.1);color:var(--GB);border:1px solid rgba(255,214,10,.2)">WINGS PR_0112</div></div>
            <div class="jn">JOIN: <code style="color:var(--GB)">project</code>=<code style="color:var(--GB)">project_number</code> <b>AND</b> <code style="color:var(--GB)">tail_number</code>=<code style="color:var(--GB)">tail_number</code></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">FC</span><span class="ca">←</span><span class="cs" style="color:var(--GB)">aircraft_csn</span></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">FH</span><span class="ca">←</span><span class="cs" style="color:var(--GB)">aircraft_tsn</span></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">EQUIP</span><span class="ca">←</span><span class="cs" style="color:var(--GB)">engine_type (primary)</span></div>
          </div>
          <div class="ms">
            <div class="msh"><div class="msd" style="background:var(--GD)"></div><div class="mst" style="color:var(--GD)">JOIN D → work_card_materials</div><div class="msb" style="background:rgba(214,58,249,.1);color:var(--GD);border:1px solid rgba(214,58,249,.2)">IC_0097 CSV</div></div>
            <div class="jn">JOIN: <code style="color:var(--GD)">project+work_order+item</code> = <code style="color:var(--GD)">project_number+work_order_number+item_number</code></div>
            <div class="mr"><span class="cf" style="color:#ffd60a">MATERIAL</span><span class="ca">←</span><span class="cs" style="color:var(--GD)">description / part_number</span></div>
          </div>
        </div>
      </div>
      <div style="margin-top:12px;font-size:9px;color:var(--dim)">* EQUIP: priority <code style="color:var(--GB)">projects.engine_type</code>, fallback — <code style="color:var(--GA)">aircrafts.engine_type</code></div>
    </div>
  </div>

  <!-- TAB 3 FLOW -->
  <div id="stc-flow" class="tc">
    <div style="padding:20px 36px;overflow:auto">
      <svg id="schema-flow-svg" width="960" height="600"></svg>
    </div>
  </div>
</div>

<script>
function schemaSw(t){
  ['erd','map','flow'].forEach((n,i)=>{
    document.querySelectorAll('.schema-wrap .tab')[i].classList.toggle('active',n===t);
    document.getElementById('stc-'+n).classList.toggle('active',n===t);
  });
  if(t==='erd') schemaDrawErd();
  if(t==='flow') schemaDrawFlow();
}

const SGRP = {
  A:{col:'#a8ff3e', label:'JOIN A'},
  B:{col:'#ffd60a', label:'JOIN B'},
  C:{col:'#ff9040', label:'JOIN C'},
  D:{col:'#d63af9', label:'JOIN D'},
};

const KEY_CONNS = [
  {from:'work_cards_master',fF:'tail_number',    to:'aircrafts',           tF:'tail_number',     grp:'A', primary:true,  lbl:'JOIN A: MSN/AGE/EQUIP'},
  {from:'work_cards_master',fF:'project',        to:'projects',            tF:'project_number',  grp:'B', primary:true,  lbl:'JOIN B: FC/FH/EQUIP'},
  {from:'work_cards_master',fF:'tail_number',    to:'projects',            tF:'tail_number',     grp:'B', primary:false},
  {from:'work_cards_master',fF:'project',        to:'eef_registry',        tF:'project_no',      grp:'C', primary:true,  lbl:'JOIN C: EEF#/DATA SRC'},
  {from:'work_cards_master',fF:'work_order',     to:'eef_registry',        tF:'nrc_number',      grp:'C', primary:false},
  {from:'work_cards_master',fF:'item',           to:'eef_registry',        tF:'nrc_number',      grp:'C', primary:false},
  {from:'work_cards_master',fF:'project',        to:'work_card_materials', tF:'project_number',  grp:'D', primary:true,  lbl:'JOIN D: MATERIAL'},
  {from:'work_cards_master',fF:'work_order',     to:'work_card_materials', tF:'work_order_number',grp:'D',primary:false},
  {from:'work_cards_master',fF:'item',           to:'work_card_materials', tF:'item_number',     grp:'D', primary:false},
];

const RET_CONNS = [
  {from:'aircrafts',           fF:'serial_number',          to:'work_cards_master', tF:'MSN',       grp:'A'},
  {from:'aircrafts',           fF:'manufactured',           to:'work_cards_master', tF:'AGE',       grp:'A'},
  {from:'projects',            fF:'aircraft_csn',           to:'work_cards_master', tF:'FC',        grp:'B'},
  {from:'projects',            fF:'aircraft_tsn',           to:'work_cards_master', tF:'FH',        grp:'B'},
  {from:'projects',            fF:'engine_type',            to:'work_cards_master', tF:'EQUIPMENT', grp:'B'},
  {from:'aircrafts',           fF:'engine_type',            to:'work_cards_master', tF:'EQUIPMENT', grp:'A'},
  {from:'eef_registry',        fF:'eef_number',             to:'work_cards_master', tF:'EEF#',      grp:'C'},
  {from:'eef_registry',        fF:'inspection_source_task', to:'work_cards_master', tF:'DATA SRC',  grp:'C'},
  {from:'work_card_materials', fF:'description',            to:'work_cards_master', tF:'MATERIAL',  grp:'D'},
];

const SFG = {};
[...KEY_CONNS,...RET_CONNS].forEach(c=>{
  [[c.from,c.fF],[c.to,c.tF]].forEach(([tbl,fld])=>{
    const k=tbl+'|'+fld;
    if(!SFG[k]) SFG[k]=[];
    if(SGRP[c.grp] && !SFG[k].includes(c.grp)) SFG[k].push(c.grp);
  });
});
function sGetGrps(tbl,fld){ return SFG[tbl+'|'+fld]||[]; }

const STW=222, SRH=20, SHH=46, SPAD=8, SSEP=22;

const STABLES = {
  work_cards_master:{label:'work_cards_master', note:'RC_master_data → renamed', col:'#00e5ff',
    fields:[
      {n:'id',pk:true},{n:'project'},{n:'project_type'},{n:'aircraft_type'},{n:'tail_number'},
      {n:'wo_station'},{n:'work_order'},{n:'item'},{n:'src_order'},{n:'src_item'},
      {n:'src_cust_card'},{n:'description'},{n:'corrective_action'},{n:'ata'},{n:'cust_card'},
      {n:'order_type'},{n:'avg_time'},{n:'act_time'},{n:'aircraft_location'},
      {sep:true},
      {n:'MSN',computed:true,grp:'A'},{n:'AGE',computed:true,grp:'A'},
      {n:'FC',computed:true,grp:'B'},{n:'FH',computed:true,grp:'B'},
      {n:'EEF#',computed:true,grp:'C'},{n:'DATA SRC',computed:true,grp:'C'},
      {n:'MATERIAL',computed:true,grp:'D'},{n:'EQUIPMENT',computed:true,grp:'B'},
    ]},
  eef_registry:{label:'eef_registry', note:'ENGINEERING ENQUIRY FORM', col:'#ff9040',
    fields:[
      {n:'id',pk:true},{n:'eef_number',ret:true},{n:'nrc_number'},{n:'ac_type'},{n:'ata'},
      {n:'project_no'},{n:'subject'},{n:'remarks'},{n:'inspection_source_task',ret:true},
      {n:'eef_status'},{n:'rc_number'},{n:'open_date'},{n:'man_hours'},{n:'eef_with'},
      {n:'project_status'},{n:'project_status2'},{n:'project_status3'},
    ]},
  aircrafts:{label:'aircrafts', note:'PR_0030 + Airfleets', col:'#a8ff3e',
    fields:[
      {n:'id',pk:true},{n:'serial_number',ret:true},{n:'tail_number'},
      {n:'aircraft_type'},{n:'engine_type',ret:true},{n:'manufactured',ret:true},
      {n:'customer_number'},{n:'delivery_date'},{n:'etops'},
    ]},
  projects:{label:'projects', note:'PR_0112 WINGS', col:'#ffd60a',
    fields:[
      {n:'id',pk:true},{n:'project_number'},{n:'tail_number'},{n:'aircraft_type'},
      {n:'aircraft_tsn',ret:true},{n:'aircraft_csn',ret:true},{n:'engine_type',ret:true},
      {n:'status'},{n:'customer_name'},{n:'open_date'},
    ]},
  work_card_materials:{label:'work_card_materials', note:'IC_0097 Material Data', col:'#d63af9',
    fields:[
      {n:'id',pk:true},{n:'project_number'},{n:'work_order_number'},{n:'item_number'},
      {n:'zone_number'},{n:'wip_status'},{n:'card_description'},{n:'tail_number'},
      {n:'part_number'},{n:'description',ret:true},{n:'status'},
    ]},
};

function sTableH(key){
  let h=SHH+SPAD;
  STABLES[key].fields.forEach(f=>{ h += f.sep ? SSEP : SRH; });
  return h;
}

const SPOS = {
  work_cards_master:   {x:22,  y:40},
  eef_registry:        {x:330, y:40},
  aircrafts:           {x:640, y:40},
  projects:            {x:640, y:300},
  work_card_materials: {x:640, y:580},
};

function sFieldY(tableKey, fieldName){
  const p=SPOS[tableKey];
  let cy=p.y+SHH;
  for(const f of STABLES[tableKey].fields){
    if(f.sep){ cy+=SSEP; continue; }
    if(f.n===fieldName) return cy+SRH/2;
    cy+=SRH;
  }
  return p.y+SHH+SRH;
}
function sPortR(tbl,fld){ return {x:SPOS[tbl].x+STW, y:sFieldY(tbl,fld)}; }
function sPortL(tbl,fld){ return {x:SPOS[tbl].x,    y:sFieldY(tbl,fld)}; }

function schemaDrawErd(){
  const svg=document.getElementById('schema-erd-svg');
  svg.innerHTML='';
  const ns='http://www.w3.org/2000/svg';

  let maxX=0,maxY=0;
  for(const[k,p] of Object.entries(SPOS)){
    if(p.x+STW+18>maxX) maxX=p.x+STW+18;
    if(p.y+sTableH(k)+18>maxY) maxY=p.y+sTableH(k)+18;
  }
  svg.setAttribute('width',maxX);
  svg.setAttribute('height',maxY);

  const defs=document.createElementNS(ns,'defs');
  const arrows={'A':'#a8ff3e','B':'#ffd60a','C':'#ff9040','D':'#d63af9'};
  Object.entries(arrows).forEach(([id,col])=>{
    const m=document.createElementNS(ns,'marker');
    m.setAttribute('id','sar-'+id);m.setAttribute('markerWidth','7');m.setAttribute('markerHeight','7');
    m.setAttribute('refX','6');m.setAttribute('refY','3');m.setAttribute('orient','auto');
    const p=document.createElementNS(ns,'path');p.setAttribute('d','M0,0 L0,6 L7,3 z');p.setAttribute('fill',col);
    m.appendChild(p);defs.appendChild(m);
    const m2=document.createElementNS(ns,'marker');
    m2.setAttribute('id','sret-'+id);m2.setAttribute('markerWidth','9');m2.setAttribute('markerHeight','9');
    m2.setAttribute('refX','7');m2.setAttribute('refY','4');m2.setAttribute('orient','auto');
    const p2=document.createElementNS(ns,'path');
    p2.setAttribute('d','M0,4 L4,0 L8,4 L4,8 z');
    p2.setAttribute('fill','none');p2.setAttribute('stroke',col);p2.setAttribute('stroke-width','1.5');
    m2.appendChild(p2);defs.appendChild(m2);
  });
  svg.appendChild(defs);

  const lLines=document.createElementNS(ns,'g');
  const lLabels=document.createElementNS(ns,'g');
  const lTables=document.createElementNS(ns,'g');
  svg.appendChild(lLines);svg.appendChild(lLabels);svg.appendChild(lTables);

  function drawBez(fp,tp,col,sw2,dash,arrowId,opacity){
    const path=document.createElementNS(ns,'path');
    const dx=Math.max(30,Math.abs(tp.x-fp.x)*0.44);
    const dirX=tp.x>fp.x?1:-1;
    path.setAttribute('d',`M${fp.x},${fp.y} C${fp.x+dirX*dx},${fp.y} ${tp.x-dirX*dx},${tp.y} ${tp.x},${tp.y}`);
    path.setAttribute('fill','none');path.setAttribute('stroke',col);
    path.setAttribute('stroke-width',sw2.toString());
    if(dash) path.setAttribute('stroke-dasharray',dash);
    path.setAttribute('marker-end',`url(#${arrowId})`);
    path.setAttribute('opacity',opacity.toString());
    return path;
  }

  KEY_CONNS.forEach(c=>{
    const col=SGRP[c.grp]?.col||'#94a3b8';
    const fromRight=SPOS[c.from].x < SPOS[c.to].x;
    const fp=fromRight?sPortR(c.from,c.fF):sPortL(c.from,c.fF);
    const tp=fromRight?sPortL(c.to,c.tF):sPortR(c.to,c.tF);
    const isPrim=c.primary!==false;
    const path=drawBez(fp,tp,col,isPrim?1.8:1.2,isPrim?null:'8,4',`sar-${c.grp}`,isPrim?0.75:0.4);
    lLines.appendChild(path);
    if(c.lbl && isPrim){
      const mx=(fp.x+tp.x)/2, my=(fp.y+tp.y)/2;
      const bw=c.lbl.length*5.6+22;
      const bg=document.createElementNS(ns,'rect');
      bg.setAttribute('x',mx-bw/2);bg.setAttribute('y',my-12);
      bg.setAttribute('width',bw);bg.setAttribute('height',13);
      bg.setAttribute('rx','3');bg.setAttribute('fill','#0a0d14');bg.setAttribute('opacity','0.9');
      lLabels.appendChild(bg);
      const badgeBg=document.createElementNS(ns,'rect');
      badgeBg.setAttribute('x',mx-bw/2);badgeBg.setAttribute('y',my-12);
      badgeBg.setAttribute('width',14);badgeBg.setAttribute('height',13);
      badgeBg.setAttribute('rx','3');badgeBg.setAttribute('fill',col);badgeBg.setAttribute('opacity','0.2');
      lLabels.appendChild(badgeBg);
      const bt=document.createElementNS(ns,'text');
      bt.setAttribute('x',mx-bw/2+7);bt.setAttribute('y',my-2);
      bt.setAttribute('text-anchor','middle');bt.setAttribute('fill',col);
      bt.setAttribute('font-size','8');bt.setAttribute('font-weight','700');bt.setAttribute('font-family','JetBrains Mono,monospace');
      bt.textContent=c.grp;lLabels.appendChild(bt);
      const t=document.createElementNS(ns,'text');
      t.setAttribute('x',mx+4);t.setAttribute('y',my-2);
      t.setAttribute('text-anchor','middle');t.setAttribute('fill',col);
      t.setAttribute('font-size','9');t.setAttribute('font-weight','700');t.setAttribute('font-family','JetBrains Mono,monospace');
      t.textContent=c.lbl.split(': ')[1]||c.lbl;lLabels.appendChild(t);
    }
  });

  RET_CONNS.forEach(c=>{
    const col=SGRP[c.grp]?.col||'#94a3b8';
    const fp=sPortL(c.from,c.fF);
    const tp=sPortR(c.to,c.tF);
    const path=drawBez(fp,tp,col,1.4,'5,3',`sret-${c.grp}`,0.65);
    lLines.appendChild(path);
  });

  Object.entries(SPOS).forEach(([key,pos])=>{
    const def=STABLES[key];
    const col=def.col;
    const h=sTableH(key);
    const isMaster=key==='work_cards_master';
    const g=document.createElementNS(ns,'g');

    const sh=document.createElementNS(ns,'rect');
    sh.setAttribute('x',pos.x+3);sh.setAttribute('y',pos.y+3);
    sh.setAttribute('width',STW);sh.setAttribute('height',h);sh.setAttribute('rx','7');
    sh.setAttribute('fill','rgba(0,0,0,0.4)');g.appendChild(sh);

    const body=document.createElementNS(ns,'rect');
    body.setAttribute('x',pos.x);body.setAttribute('y',pos.y);
    body.setAttribute('width',STW);body.setAttribute('height',h);body.setAttribute('rx','7');
    body.setAttribute('fill','#111827');body.setAttribute('stroke',col);
    body.setAttribute('stroke-width',isMaster?'2.5':'1.5');
    g.appendChild(body);

    const hbg=document.createElementNS(ns,'rect');
    hbg.setAttribute('x',pos.x);hbg.setAttribute('y',pos.y);
    hbg.setAttribute('width',STW);hbg.setAttribute('height',SHH);hbg.setAttribute('rx','7');
    hbg.setAttribute('fill',col);hbg.setAttribute('opacity',isMaster?'0.22':'0.12');
    g.appendChild(hbg);
    const hbg2=document.createElementNS(ns,'rect');
    hbg2.setAttribute('x',pos.x);hbg2.setAttribute('y',pos.y+SHH-6);
    hbg2.setAttribute('width',STW);hbg2.setAttribute('height',6);
    hbg2.setAttribute('fill','#111827');hbg2.setAttribute('opacity','0.55');
    g.appendChild(hbg2);
    const hl=document.createElementNS(ns,'line');
    hl.setAttribute('x1',pos.x);hl.setAttribute('y1',pos.y+SHH);
    hl.setAttribute('x2',pos.x+STW);hl.setAttribute('y2',pos.y+SHH);
    hl.setAttribute('stroke',col);hl.setAttribute('stroke-width','1');hl.setAttribute('opacity','0.3');
    g.appendChild(hl);

    const title=document.createElementNS(ns,'text');
    title.setAttribute('x',pos.x+STW/2);title.setAttribute('y',pos.y+17);
    title.setAttribute('text-anchor','middle');title.setAttribute('fill',col);
    title.setAttribute('font-size',isMaster?'12':'11');
    title.setAttribute('font-weight','700');title.setAttribute('font-family','JetBrains Mono,monospace');
    title.textContent=def.label;g.appendChild(title);

    const note=document.createElementNS(ns,'text');
    note.setAttribute('x',pos.x+STW/2);note.setAttribute('y',pos.y+32);
    note.setAttribute('text-anchor','middle');note.setAttribute('fill','#475569');
    note.setAttribute('font-size','8');note.setAttribute('font-family','JetBrains Mono,monospace');
    note.textContent=def.note;g.appendChild(note);

    let curY=pos.y+SHH;
    def.fields.forEach((field,i)=>{
      if(field.sep){
        const sepBg=document.createElementNS(ns,'rect');
        sepBg.setAttribute('x',pos.x);sepBg.setAttribute('y',curY);
        sepBg.setAttribute('width',STW);sepBg.setAttribute('height',SSEP);
        sepBg.setAttribute('fill','rgba(255,214,10,0.06)');g.appendChild(sepBg);
        const sepLine=document.createElementNS(ns,'line');
        sepLine.setAttribute('x1',pos.x);sepLine.setAttribute('y1',curY);
        sepLine.setAttribute('x2',pos.x+STW);sepLine.setAttribute('y2',curY);
        sepLine.setAttribute('stroke','#ffd60a');sepLine.setAttribute('stroke-width','1');
        sepLine.setAttribute('opacity','0.35');g.appendChild(sepLine);
        const sepIcon=document.createElementNS(ns,'text');
        sepIcon.setAttribute('x',pos.x+8);sepIcon.setAttribute('y',curY+SSEP/2+4);
        sepIcon.setAttribute('fill','#ffd60a');sepIcon.setAttribute('font-size','8');
        sepIcon.setAttribute('font-weight','700');sepIcon.setAttribute('font-family','JetBrains Mono,monospace');
        sepIcon.textContent='▼ COMPUTED @ DISPLAY (not stored in DB)';g.appendChild(sepIcon);
        curY+=SSEP; return;
      }

      const fy=curY;
      if(i%2===0){
        const rb=document.createElementNS(ns,'rect');
        rb.setAttribute('x',pos.x+1);rb.setAttribute('y',fy);
        rb.setAttribute('width',STW-2);rb.setAttribute('height',SRH);
        rb.setAttribute('fill',field.computed?'rgba(255,214,10,0.04)':'rgba(255,255,255,0.016)');
        g.appendChild(rb);
      }
      if(field.pk){
        const b=document.createElementNS(ns,'text');b.setAttribute('x',pos.x+7);b.setAttribute('y',fy+SRH/2+4);
        b.setAttribute('fill','#ffd60a');b.setAttribute('font-size','8');b.setAttribute('font-weight','700');b.setAttribute('font-family','JetBrains Mono,monospace');
        b.textContent='PK';g.appendChild(b);
      } else if(field.fk){
        const b=document.createElementNS(ns,'text');b.setAttribute('x',pos.x+7);b.setAttribute('y',fy+SRH/2+4);
        b.setAttribute('fill','#ff4d4d');b.setAttribute('font-size','8');b.setAttribute('font-weight','700');b.setAttribute('font-family','JetBrains Mono,monospace');
        b.textContent='FK';g.appendChild(b);
      } else if(field.computed){
        const gcol=SGRP[field.grp]?.col||'#ffd60a';
        const gb=document.createElementNS(ns,'rect');
        gb.setAttribute('x',pos.x+7);gb.setAttribute('y',fy+4);
        gb.setAttribute('width',13);gb.setAttribute('height',SRH-8);gb.setAttribute('rx','2');
        gb.setAttribute('fill',gcol);gb.setAttribute('opacity','0.25');g.appendChild(gb);
        const gt=document.createElementNS(ns,'text');
        gt.setAttribute('x',pos.x+13);gt.setAttribute('y',fy+SRH/2+4);
        gt.setAttribute('text-anchor','middle');gt.setAttribute('fill',gcol);
        gt.setAttribute('font-size','8');gt.setAttribute('font-weight','700');gt.setAttribute('font-family','JetBrains Mono,monospace');
        gt.textContent=field.grp;g.appendChild(gt);
      } else if(field.ret){
        const r=document.createElementNS(ns,'text');r.setAttribute('x',pos.x+8);r.setAttribute('y',fy+SRH/2+4);
        r.setAttribute('fill','#ffd60a');r.setAttribute('font-size','9');r.setAttribute('font-family','JetBrains Mono,monospace');
        r.textContent='↑';g.appendChild(r);
      }

      const grps=sGetGrps(key,field.n||'');
      grps.forEach((gId,gi)=>{
        const d=document.createElementNS(ns,'circle');
        d.setAttribute('cx',pos.x+STW-7-gi*11);d.setAttribute('cy',fy+SRH/2);
        d.setAttribute('r','3.5');d.setAttribute('fill',SGRP[gId]?.col||'#94a3b8');d.setAttribute('opacity','0.9');g.appendChild(d);
      });

      const ox=(field.pk||field.fk)?pos.x+24:field.computed?pos.x+26:field.ret?pos.x+22:pos.x+9;
      const ft=document.createElementNS(ns,'text');
      ft.setAttribute('x',ox);ft.setAttribute('y',fy+SRH/2+4);
      let fc='#94a3b8';
      if(field.pk) fc='#ffd60a';
      else if(field.fk) fc='#ff4d4d';
      else if(field.computed) fc=SGRP[field.grp]?.col||'#ffd60a';
      else if(grps.length>0) fc=SGRP[grps[0]]?.col||'#94a3b8';
      else if(field.ret) fc='#ffd60a';
      ft.setAttribute('fill',fc);
      ft.setAttribute('font-size',field.computed?'11':'10');
      if(field.computed) ft.setAttribute('font-weight','700');
      ft.setAttribute('font-family','JetBrains Mono,monospace');
      ft.textContent=field.n;g.appendChild(ft);
      curY+=SRH;
    });
    lTables.appendChild(g);
  });
}

function schemaDrawFlow(){
  const svg=document.getElementById('schema-flow-svg');
  svg.innerHTML='';
  const ns='http://www.w3.org/2000/svg';
  svg.setAttribute('width','950');svg.setAttribute('height','570');

  const defs=document.createElementNS(ns,'defs');
  [['cy','#00e5ff'],['or','#ff9040'],['gr','#a8ff3e'],['yw','#ffd60a'],['pu','#d63af9'],['bl','#4e9af1']].forEach(([id,col])=>{
    const m=document.createElementNS(ns,'marker');
    m.setAttribute('id','sfa-'+id);m.setAttribute('markerWidth','8');m.setAttribute('markerHeight','8');
    m.setAttribute('refX','7');m.setAttribute('refY','3');m.setAttribute('orient','auto');
    const p=document.createElementNS(ns,'path');p.setAttribute('d','M0,0 L0,6 L8,3 z');p.setAttribute('fill',col);
    m.appendChild(p);defs.appendChild(m);
  });
  svg.appendChild(defs);

  function bx(x,y,w,h,col,l1,l2,thick=false){
    const r=document.createElementNS(ns,'rect');r.setAttribute('x',x);r.setAttribute('y',y);r.setAttribute('width',w);r.setAttribute('height',h);r.setAttribute('rx','7');r.setAttribute('fill','#111827');r.setAttribute('stroke',col);r.setAttribute('stroke-width',thick?'2':'1.2');svg.appendChild(r);
    const t=document.createElementNS(ns,'text');t.setAttribute('x',x+w/2);t.setAttribute('y',y+(l2?16:h/2+5));t.setAttribute('text-anchor','middle');t.setAttribute('fill',col);t.setAttribute('font-size','11');t.setAttribute('font-weight','700');t.setAttribute('font-family','JetBrains Mono,monospace');t.textContent=l1;svg.appendChild(t);
    if(l2){const t2=document.createElementNS(ns,'text');t2.setAttribute('x',x+w/2);t2.setAttribute('y',y+31);t2.setAttribute('text-anchor','middle');t2.setAttribute('fill','#475569');t2.setAttribute('font-size','9');t2.setAttribute('font-family','JetBrains Mono,monospace');t2.textContent=l2;svg.appendChild(t2);}
  }
  function ar(x1,y1,x2,y2,col,aid,dash){
    const dx=Math.abs(x2-x1)*0.42;const p=document.createElementNS(ns,'path');p.setAttribute('d',`M${x1},${y1} C${x1+dx},${y1} ${x2-dx},${y2} ${x2},${y2}`);p.setAttribute('fill','none');p.setAttribute('stroke',col);p.setAttribute('stroke-width','1.8');p.setAttribute('marker-end',`url(#${aid})`);p.setAttribute('opacity','0.82');if(dash)p.setAttribute('stroke-dasharray',dash);svg.appendChild(p);
  }
  function lbl(x,y,t,col,sz=10){const tx=document.createElementNS(ns,'text');tx.setAttribute('x',x);tx.setAttribute('y',y);tx.setAttribute('fill',col||'#64748b');tx.setAttribute('font-size',sz);tx.setAttribute('font-family','JetBrains Mono,monospace');tx.textContent=t;svg.appendChild(tx);}

  lbl(18,22,'SOURCES','#475569',10);lbl(340,22,'IMPORT','#475569',10);lbl(680,22,'DB TABLES','#475569',10);
  [330,665].forEach(x=>{const l=document.createElementNS(ns,'line');l.setAttribute('x1',x);l.setAttribute('y1',30);l.setAttribute('x2',x);l.setAttribute('y2',550);l.setAttribute('stroke','#1e293b');l.setAttribute('stroke-width','1');svg.appendChild(l);});

  const SRCS=[
    {y:48, col:'#4e9af1',l1:'WINGS Work Card Inquiry',l2:'CSV / Excel (Work Cards)'},
    {y:148,col:'#ff9040',l1:'EEF Form Register (Excel)',l2:'ENGINEERING ENQUIRY FORM'},
    {y:248,col:'#d63af9',l1:'IC_0097 Material Data',l2:'CSV Export'},
    {y:348,col:'#a8ff3e',l1:'PR_0030 Airfleets',l2:'Aircraft Data (WINGS)'},
    {y:448,col:'#ffd60a',l1:'PR_0112 Projects',l2:'Project Data (WINGS)'}
  ];
  SRCS.forEach(s=>bx(18,s.y,278,50,s.col,s.l1,s.l2));

  const IMPS=[
    {y:55, col:'#00e5ff',l1:'importWorkCards()',l2:'→ work_cards_master (RC/NRC)'},
    {y:100,col:'#4e9af1',l1:'importWorkCards()',l2:'→ work_cards (operational)'},
    {y:155,col:'#ff9040',l1:'importEefRegistry()',l2:'→ eef_registry'},
    {y:255,col:'#d63af9',l1:'importMaterials()',l2:'→ work_card_materials'},
    {y:355,col:'#a8ff3e',l1:'importAircrafts()',l2:'→ aircrafts'},
    {y:455,col:'#ffd60a',l1:'importProjects()',l2:'→ projects'}
  ];
  IMPS.forEach(i=>bx(338,i.y,288,44,i.col,i.l1,i.l2));

  const DBS=[
    {y:48, col:'#00e5ff',l:'work_cards_master',thick:true},
    {y:107,col:'#4e9af1',l:'work_cards'},
    {y:160,col:'#ff9040',l:'eef_registry'},
    {y:260,col:'#d63af9',l:'work_card_materials'},
    {y:360,col:'#a8ff3e',l:'aircrafts'},
    {y:460,col:'#ffd60a',l:'projects'}
  ];
  DBS.forEach(d=>bx(675,d.y,240,44,d.col,d.l,null,d.thick));

  ar(296,SRCS[0].y+25,338,IMPS[0].y+22,'#00e5ff','sfa-cy');
  ar(296,SRCS[0].y+25,338,IMPS[1].y+22,'#4e9af1','sfa-bl','5,3');
  ar(296,SRCS[1].y+25,338,IMPS[2].y+22,'#ff9040','sfa-or');
  ar(296,SRCS[2].y+25,338,IMPS[3].y+22,'#d63af9','sfa-pu');
  ar(296,SRCS[3].y+25,338,IMPS[4].y+22,'#a8ff3e','sfa-gr');
  ar(296,SRCS[4].y+25,338,IMPS[5].y+22,'#ffd60a','sfa-yw');

  const aids=['sfa-cy','sfa-bl','sfa-or','sfa-pu','sfa-gr','sfa-yw'];
  IMPS.forEach((im,i)=>ar(626,im.y+22,675,DBS[i].y+22,im.col,aids[i]));

  lbl(18,545,'* Enrichment happens on every request — MSN, AGE, FC, FH, EEF#, DATA SRC, MATERIAL are NOT stored in DB','#475569',9);
}

document.addEventListener('DOMContentLoaded', ()=>schemaDrawErd());
</script>
@endsection

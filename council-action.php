<?php require_once __DIR__ . '/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Council Action">
<title>UTMC Council Action Tracker</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
  :root {
    --ncc-blue: #444970;
    --ncc-teal: #56F0D1;
    --ncc-grey: #ECEAEA;
    --navy: #444970;
    --navy-dark: #333860;
    --accent: #444970;
    --accent-light: #ECEAEA;
    --bg: #F4F5F7;
    --white: #FFFFFF;
    --border: #E2E4EA;
    --text: #1A1A2E;
    --text-mid: #4A5568;
    --text-light: #718096;
    --green: #059669;
    --green-bg: #ECFDF5;
    --red: #DC2626;
    --red-bg: #FEF2F2;
    --amber: #D97706;
    --amber-bg: #FFFBEB;
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Lexend', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--bg); color: var(--text); font-weight: 400; }
  
  .topbar {
    background: linear-gradient(135deg, var(--ncc-blue) 0%, #333860 100%);
    color: white; padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
    position: sticky; top: 0; z-index: 100;
    box-shadow: 0 2px 8px rgba(68,73,112,0.3);
  }
  .topbar h1 { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
  .topbar .subtitle { font-size: 12px; opacity: 0.6; font-weight: 300; margin-top: 2px; }
  .topbar a { color: var(--ncc-teal); text-decoration: none; font-size: 13px; font-weight: 500; }
  .topbar a:hover { color: white; }
  
  .controls {
    background: var(--white); border-bottom: 1px solid var(--border);
    padding: 14px 24px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;
    position: sticky; top: 58px; z-index: 99;
  }
  .controls select, .controls input {
    padding: 9px 14px; border: 1px solid var(--border); border-radius: 8px;
    font-size: 14px; background: var(--white); font-family: inherit;
    transition: border-color 0.2s;
  }
  .controls select:focus, .controls input:focus { border-color: var(--ncc-blue); outline: none; box-shadow: 0 0 0 3px rgba(68,73,112,0.1); }
  .controls select { min-width: 180px; }
  .controls input { flex: 1; min-width: 200px; }

  .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

  /* Stats dashboard */
  .stats-row {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px;
    margin-bottom: 24px;
  }
  .stat-card {
    background: var(--white); border-radius: 10px; padding: 18px 20px;
    border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  }
  .stat-card .stat-number { font-size: 28px; font-weight: 700; color: var(--ncc-blue); line-height: 1; }
  .stat-card .stat-label { font-size: 12px; color: var(--text-light); margin-top: 4px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }
  .stat-card.stat-pending .stat-number { color: #6B7280; }
  .stat-card.stat-progress .stat-number { color: var(--amber); }
  .stat-card.stat-completed .stat-number { color: var(--green); }
  .stat-card.stat-total .stat-number { color: var(--ncc-blue); }
  
  .cat-section { margin-bottom: 16px; }
  .cat-header {
    display: flex; align-items: center; gap: 12px; padding: 14px 18px;
    background: var(--white); border-radius: 10px 10px 0 0; border: 1px solid var(--border);
    cursor: pointer; user-select: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: background 0.15s;
  }
  .cat-header:hover { background: #f8f9fb; }
  .cat-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 8px;
    color: white; font-size: 13px; font-weight: 700;
  }
  .cat-name { font-weight: 600; font-size: 15px; flex: 1; }
  .cat-progress {
    width: 80px; height: 6px; background: #E5E7EB; border-radius: 3px; overflow: hidden;
  }
  .cat-progress-bar { height: 100%; background: var(--green); border-radius: 3px; transition: width 0.3s; }
  .cat-progress-text { font-size: 11px; color: var(--text-light); width: 36px; text-align: right; }
  .cat-toggle { font-size: 18px; color: var(--text-light); transition: transform 0.2s; margin-left: 4px; }
  .cat-section.collapsed .cat-toggle { transform: rotate(-90deg); }
  .cat-section.collapsed .cat-body { display: none; }

  .cat-body {
    border: 1px solid var(--border); border-top: none;
    border-radius: 0 0 10px 10px; overflow: hidden;
  }
  
  .fault-card {
    padding: 18px 20px; border-bottom: 1px solid #eef0f4;
    background: var(--white); transition: background 0.15s;
  }
  .fault-card:last-child { border-bottom: none; }
  .fault-card:hover { background: #FAFBFD; }
  .fault-card.status-completed { background: var(--green-bg); border-left: 4px solid var(--green); }
  .fault-card.status-in_progress { background: var(--amber-bg); border-left: 4px solid var(--amber); }
  .fault-card.status-escalated { background: var(--red-bg); border-left: 4px solid var(--red); }

  .fault-top {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 12px; margin-bottom: 8px;
  }
  .fault-site { font-weight: 600; font-size: 16px; color: var(--text); letter-spacing: -0.2px; }
  .status-pill {
    display: inline-block; padding: 4px 12px; border-radius: 12px;
    font-size: 12px; font-weight: 600; white-space: nowrap; flex-shrink: 0;
  }
  .status-pill.pending { background: #E5E7EB; color: #374151; }
  .status-pill.in_progress { background: #FDE68A; color: #92400E; }
  .status-pill.completed { background: #A7F3D0; color: #065F46; }
  .status-pill.escalated { background: #FECACA; color: #991B1B; }

  .fault-summary {
    font-size: 14px; color: var(--text-mid); line-height: 1.5;
    margin-bottom: 8px;
  }
  .fault-codes {
    font-size: 12px; color: var(--ncc-blue); background: #F0F1F5;
    padding: 5px 10px; border-radius: 4px; display: inline-block;
    margin-bottom: 10px; font-weight: 500;
  }

  .fault-notes-display {
    background: #FFF7ED; border-left: 3px solid #F59E0B; color: #92400E;
    padding: 10px 14px; border-radius: 6px; margin-bottom: 10px;
    font-size: 14px; line-height: 1.5;
  }
  .fault-notes-display strong { color: #B45309; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px; }

  .fault-footer {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
  }
  .fault-footer select {
    padding: 7px 12px; border: 1px solid var(--border); border-radius: 6px;
    font-size: 13px; background: var(--white); min-width: 150px;
  }
  .btn-sm {
    padding: 7px 14px; border: 1px solid var(--border); border-radius: 6px;
    font-size: 13px; background: var(--white); cursor: pointer; color: var(--text-mid);
  }
  .btn-sm:hover { background: #f0f2f5; }

  .fault-detail {
    display: none; margin-top: 10px; padding: 12px 14px;
    background: #F8F9FC; border-radius: 6px; font-size: 12px;
    color: var(--text-light); line-height: 1.6;
  }
  .fault-detail.visible { display: block; }
  .fault-detail span { display: block; margin-bottom: 4px; }
  .fault-detail .detail-label { font-weight: 600; color: var(--text-mid); display: inline; }

  .note-input {
    display: none; margin-top: 10px;
  }
  .note-input.visible { display: block; }
  .note-input textarea {
    width: 100%; padding: 10px; border: 1px solid var(--border);
    border-radius: 6px; font-size: 13px; resize: vertical; min-height: 80px;
    font-family: inherit;
  }
  .note-input .note-save {
    margin-top: 8px; padding: 8px 20px; background: var(--accent); color: white;
    border: none; border-radius: 6px; font-size: 13px; cursor: pointer; font-weight: 500;
  }
  .note-input .note-save:hover { background: #2563EB; }

  .empty { text-align: center; padding: 60px 20px; color: var(--text-light); font-size: 15px; }

  /* Map */
  .map-container { margin-bottom: 20px; border-radius: 12px; overflow: hidden; border: 1px solid var(--border); box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
  #faultMap { height: 350px; width: 100%; }
  .map-bar { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 16px; background: var(--white); border-bottom: 1px solid var(--border); }
  .map-bar span { font-size: 14px; font-weight: 600; color: var(--ncc-blue); }

  /* Fault age */
  .fault-age { font-size: 12px; font-weight: 500; display: inline-block; margin-left: 8px; }
  .age-recent { color: var(--green); }
  .age-weeks { color: var(--amber); }
  .age-months { color: var(--red); }

  /* Visual polish */
  .stat-card { transition: transform 0.15s, box-shadow 0.15s; }
  .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
  .fault-card { transition: background 0.15s, box-shadow 0.15s; }
  .fault-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
  .btn-navigate { background: #1A7F4B; color: white; border-color: #1A7F4B; }
  .btn-navigate:hover { background: #15673D; }
  .btn-route { background: var(--ncc-blue); color: white; border-color: var(--ncc-blue); }
  .btn-route:hover { background: var(--navy-dark); }
  .site-checkbox { width: 20px; height: 20px; accent-color: var(--ncc-blue); cursor: pointer; flex-shrink: 0; margin-top: 2px; }
  .fault-card.selected { background: #EEF0FF; border-left: 4px solid var(--ncc-blue); }
  .selection-count { font-size: 13px; color: var(--ncc-blue); font-weight: 600; }

  /* Print */
  .print-header, .print-meta { display: none; }
  @media print {
    .topbar, .controls, .cat-filters, .map-container, .fault-footer,
    .note-input, .fault-detail, .stats-row { display: none !important; }
    body { background: white; font-size: 11pt; }
    .container { max-width: 100%; padding: 0; }
    .fault-list { border: none; box-shadow: none; }
    .fault-card { padding: 10px 0; border-bottom: 1px solid #ccc; page-break-inside: avoid; box-shadow: none !important; }
    .fault-site { font-size: 13pt; }
    .print-header { display: block !important; text-align: center; margin-bottom: 16px; font-size: 16pt; font-weight: 700; color: #444970; }
    .print-meta { display: block !important; text-align: center; margin-bottom: 16px; font-size: 10pt; color: #666; }
    @page { margin: 1.5cm; }
  }

  /* Category filter buttons */
  .cat-filters {
    padding: 14px 24px; background: var(--white);
    border-bottom: 1px solid var(--border);
    display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
  }
  .cat-btn {
    padding: 6px 14px; border: 2px solid var(--border); border-radius: 20px;
    font-size: 12px; font-weight: 500; cursor: pointer;
    background: var(--white); color: var(--text-mid);
    font-family: inherit; transition: all 0.15s; white-space: nowrap;
  }
  .cat-btn:hover { border-color: var(--ncc-blue); color: var(--ncc-blue); }
  .cat-btn.active { background: var(--ncc-blue); color: white; border-color: var(--ncc-blue); }
  .cat-btn .cat-btn-count {
    display: inline-block; background: rgba(0,0,0,0.1); border-radius: 10px;
    padding: 1px 7px; font-size: 11px; margin-left: 4px;
  }
  .cat-btn.active .cat-btn-count { background: rgba(255,255,255,0.25); }

  /* Flat fault list (no category sections) */
  .fault-list {
    background: var(--white); border: 1px solid var(--border);
    border-radius: 10px; overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
  }

  @media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .controls { flex-direction: column; }
    .cat-filters { padding: 10px 16px; }
    .fault-footer { flex-direction: column; align-items: stretch; }
    .fault-footer select, .btn-sm { width: 100%; text-align: center; }
  }
</style>
</head>
<body>

<div class="topbar">
  <div style="display:flex;align-items:center;gap:16px">
    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALUAAAAuCAYAAABu8lgpAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYxIDY0LjE0MDk0OSwgMjAxMC8xMi8wNy0xMDo1NzowMSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNS4xIFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NEFBNDE4QTkyNEZBMTFFM0I2RUE5MjBDM0FDRkMwRDQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NEFBNDE4QUEyNEZBMTFFM0I2RUE5MjBDM0FDRkMwRDQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo0QUE0MThBNzI0RkExMUUzQjZFQTkyMEMzQUNGQzBENCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo0QUE0MThBODI0RkExMUUzQjZFQTkyMEMzQUNGQzBENCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PrTDFrkAACSMSURBVHja7J0LuFVz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" alt="Newcastle City Council" style="height:36px;width:auto;filter:brightness(0) invert(1);opacity:0.9">
    <div>
      <h1>Council Action Tracker</h1>
      <div class="subtitle">NCC UTMC - Fault actions grouped by category</div>
    </div>
  </div>
  <div>
    <a href="index.php">Back to dashboard</a>
    &nbsp;&nbsp;
    <a href="logout.php">Logout</a>
  </div>
</div>

<div class="controls">
  <select id="areaFilter">
    <option value="">All areas</option>
    <option>Durham</option>
    <option>Gateshead</option>
    <option>Newcastle</option>
    <option>North Tyneside</option>
    <option>Northumberland</option>
    <option>South Tyneside</option>
    <option>Sunderland</option>
  </select>
  <select id="statusFilter">
    <option value="">All statuses</option>
    <option value="pending">Pending</option>
    <option value="in_progress">In Progress</option>
    <option value="completed">Completed</option>
    <option value="escalated">Escalated</option>
  </select>
  <input type="text" id="searchInput" placeholder="Search by site, fault ID, or description...">
  <button class="btn-sm" data-action="export-csv" style="white-space:nowrap">Export CSV</button>
</div>

<div class="cat-filters" id="catFilters"></div>

<div class="container">
  <div class="print-header">Council Action - Job Sheet</div>
  <div class="print-meta" id="printMeta"></div>
  <div class="stats-row" id="statsRow"></div>
  <div class="map-container">
    <div class="map-bar">
      <span>Site locations <span class="selection-count" id="selCount"></span></span>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <button class="btn-sm" data-action="select-all">Select all</button>
        <button class="btn-sm" data-action="clear-selection">Clear</button>
        <button class="btn-sm btn-route" data-action="plan-route">Plan route</button>
        <button class="btn-sm" data-action="print-jobs">Print job sheet</button>
      </div>
    </div>
    <div id="faultMap"></div>
  </div>
  <div id="content"></div>
</div>

<script>
const API_URL = 'api/index.php?page=council_action';

// Fault data: [faultId, site, area, created, imtracStatus, category, info, closeComments, faultCode]
const FAULTS = [
['F-002484','DUR001 - County Bridge -  Barnard Castle','Durham','04/03/2026 13:56','TemporaryClear','Loop / Slot Cutting','SLB and AR7 gone faulty following loop cutting of SLB and BXYZ. Can loops be checked and det pack reset.','','TS53 - Permanent Extension;TS54 - Permanent Demand;','See comments','54.54269491289353','-1.927308195747046'],
['F-002392','DUR051 - Ramshaw Bridge -  Evenwood.','Durham','19/01/2026 11:22','TemporaryClear','Other / Needs Review','Need Aerials installing on controller','','TS80 - Other Known Faults;','See comments','54.62665505160604','-1.76924915317241'],
['F-002365','DUR148 - Gilesgate Rbt - Durham','Durham','16/12/2025 13:17','TemporaryClear','Loop / Slot Cutting','GSLB HSLA CROUT1 CPOUT1 in DFM. Can loops be tested, check twinflex and check cables.','','TS61 - Detectors (Loops) RTA/Damaged;','See comments','54.77877830846728','-1.564535228178132'],
['F-002364','DUR020 - Westlea Road (Central) -  Seaham','Durham','16/12/2025 13:12','TemporaryClear','Push Button / Pedestrian','Display unit has been vandalized. Spray painted.','','TS55 - Faulty Push Buttons;','See comments','54.8352387641927','-1.366895866118924'],
['F-002346','DUR120 - Belmont Industrial Estate','Durham','05/12/2025 10:56','TemporaryClear','Loop / Slot Cutting','DX14 IN FAULT LOG - RESET EARLIER AND RE-APPEARED - COULD LOOP TESTED.','','TS50 - Detection Faults;','See comments','54.78614553403592','-1.54201279102945'],
['F-002333','DUR060 - Crossgate Peth -  Durham City','Durham','25/11/2025 15:24','TemporaryClear','Loop / Slot Cutting','DET36 DSL GETTING STUCK ACTIVE WITH NO VEHICLE PRESENCE - LOOP TO BE TESTED. N01231F1- IN DFM, POTENTIAL FOR LOOP TAILS NOT CONNECTED TO FEEDER CABLE. N01221I1 - DETECTOR CARD MISSING.','','TS50 - Detection Faults;TS53 - Permanent Extension;','See comments','54.77499964523968','-1.584205767327973'],
['F-002316','DUR034 - Millburngate -  Durham','Durham','19/11/2025 08:35','TemporaryClear','Loop / Slot Cutting','Loop SLJ2 keeps failing active, can loop be checked','','TS50 - Detection Faults;','See comments','54.77809656162319','-1.57940943504552'],
['F-006519','4022 - Leam Lane / Lingley Lane - Gateshead','Gateshead','21/03/2026 09:55','TemporaryClear','Investigate All Out','1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments','54.93973590927226','-1.533789553759178'],
['F-006517','4002 - Oakwellgate / Nelson Street - Gateshead','Gateshead','19/03/2026 16:25','TemporaryClear','Post-RTC Repair','Stubb pole on deck.','','TS59 - Signal Heads RTA/Damaged;','See comments','54.96437524848797','-1.601708934423328'],
['F-006516','5019 - Lambton Street / Tesco - Gateshead','Gateshead','19/03/2026 15:01','TemporaryClear','Post-RTC Repair','stub pole lying flat on ground','','TS62 - Signal Poles RTA/Damaged;','See comments','54.96392399999999','-1.60343'],
['F-006503','4130 - Northside Merge - Gateshead','Gateshead','11/03/2026 07:43','TemporaryClear','Comms / Router Reboot','Router online, however, no connection to controller. Can you check ethernet cable is connected to router/controller please.','','TS72 - No Comms with OTU;','See comments','54.90586523841095','-1.561929040371169'],
['F-006502','4092 - Metro Perimeter / Cross Lane - Gateshead','Gateshead','10/03/2026 13:28','TemporaryClear','Cable / Electrical','power supply pillar corroded - has a hole in the bottom- noted whilst carrying a scheduled all other faults','','TS80 - Other Known Faults;','See comments','54.95631970442852','-1.66263465447409'],
['F-006496','4130 - Northside Merge - Gateshead','Gateshead','09/03/2026 09:01','TemporaryClear','Comms / Router Reboot','UTC showing site offline - please reboot router.','','TS70 - OTU General Fault;','See comments','54.90586523841095','-1.561929040371169'],
['F-006495','4050 - High Street / Charles Street - Gateshead','Gateshead','09/03/2026 08:52','TemporaryClear','Comms / Router Reboot','UTC showing site offline - please reboot router.','','TS70 - OTU General Fault;','See comments','54.96150111650362','-1.600360925537359'],
['F-006492','4086 - High West Street / Arthur Street - Gateshead','Gateshead','06/03/2026 22:45','TemporaryClear','Post-RTC Repair','Traffic light on junction of High West Street and Arthur Street has been hit by a car and knocked to ground. It is right beside Gateshead police station, High West Street, NE8 1BN 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','54.9583856291353','-1.601393912831782'],
['F-006490','4093 - Felling Bypass - Abbotsford Road - Gateshead','Gateshead','06/03/2026 15:24','TemporaryClear','Post-RTC Repair','rtc  found by telent engineer','','TS80 - Other Known Faults;','See comments','54.95502869574072','-1.564721922475144'],
['F-006436','5625 - Stargate Lane / Beweshill Lane - Gateshead','Gateshead','19/02/2026 10:28','TemporaryClear','Lamp / LED Replacement','lamp out','','TS80 - Other Known Faults;','See comments','54.96098695574812','-1.743287098795477'],
['F-006400','5004 - Front Street / Rectory Lane - Gateshead','Gateshead','09/02/2026 07:46','TemporaryClear','MOVA / SCOOT','MOVA not operating, stuck on VA','','TS91 - MOVA Fault;','See comments','54.94568868993313','-1.676119545179802'],
['F-006389','4130 - Northside Merge - Gateshead','Gateshead','06/02/2026 11:25','TemporaryClear','Check / Adjust Timings','Not letting enough of the traffic from the A1 through making the tailback all the way back to the angel on the A1  - will cause a massive accident as this is everyday since opening up the road from road works. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','TS80 - Other Known Faults;','See comments','54.90586523841095','-1.561929040371169'],
['F-006386','5019 - Lambton Street / Tesco - Gateshead','Gateshead','06/02/2026 08:49','TemporaryClear','Inspect / Survey','Entrance/Exit to Tesco, Lambton Street, Gateshead. What looks like a traffic signals pillar has wires showing','','TSI - Site Inspection;','See comments','54.96392399999999','-1.60343'],
['F-006360','4141 - Felling Metro Station / Sunderland Road - Gateshead','Gateshead','02/02/2026 07:10','TemporaryClear','Comms / Router Reboot','5G Router offline, please power cycle off and on again to reboot','','TS72 - No Comms with OTU;','See comments','54.95266552725376','-1.571248274535194'],
['F-006350','5016 - Thornley Drive / Rowlands Gill - Gateshead','Gateshead','29/01/2026 13:25','TemporaryClear','Realign Signal Head','Other The traffic light when you come down thornley road has been turned to look down lockhaugh road towards Winlaton mill.  Therefore dangerous as someone not knowing the area 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.93268955367341','-1.729605770264726'],
['F-006345','4004 - Felling Bypass / Green Lane - Gateshead','Gateshead','29/01/2026 06:25','TemporaryClear','Other / Needs Review','Other Lights are turning to red when approaching on the felling bypass . There is no traffic coming from green lane or the old fold estate. They used to turn red only when there was traffic coming f','','','See comments','54.95644804136213','-1.572077104673298'],
['F-006323','4002 - Oakwellgate / Nelson Street - Gateshead','Gateshead','26/01/2026 08:55','TemporaryClear','Post-RTC Repair','Signal damaged Signal damaged following RTA 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','54.96437524848797','-1.601708934423328'],
['F-006322','4067 - Bensham Road / Cuthbert Street - Gateshead','Gateshead','24/01/2026 15:25','TemporaryClear','Other / Needs Review','Other There is no way for pedestrians to press a button to cross the road from one side. There is a button on the opposite side if you are walking down the hill passing the hotel but if you are tryi','','TS80 - Other Known Faults;','See comments','54.95557407433471','-1.613867427008401'],
['F-006260','4093 - Felling Bypass - Abbotsford Road - Gateshead','Gateshead','21/01/2026 22:25','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) The traffic lights heading towards Heworth are out of sync.  They are mostly on red and the side road with limited traffic','-','-','See comments','54.95502869574072','-1.564721922475144'],
['F-006252','5007 - A692 / Potters Wheel - Gateshead','Gateshead','20/01/2026 11:55','TemporaryClear','Other / Needs Review','[Please check notes for rest of description]Other When travelling north west on the A6076, the lights used to change on approach if the was no traffic on Gatehead Road. They then changed so you have','-','TS80 - Other Known Faults;','See comments','54.92210567243283','-1.675577806333379'],
['F-006236','5630 - A694 / Noel Avenue - Gateshead','Gateshead','15/01/2026 10:35','TemporaryClear','Post-RTC Repair','Signal damaged Vehicle has collided with traffic lights causing damage. The lights are wonky and will need to be assessed. Northumbria Police aware, incident NP-20260115-0269 refers','-','-','See comments','54.94319751806079','-1.710817631803167'],
['F-006179','4022 - Leam Lane / Lingley Lane - Gateshead','Gateshead','22/12/2025 10:05','TemporaryClear','Post-RTC Repair','Signal damaged Pole damaged and lying on its side across pedestrian crossing with wires exposed 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATIO','-','-','See comments','54.93973590927226','-1.533789553759178'],
['F-006167','5004 - Front Street / Rectory Lane - Gateshead','Gateshead','16/12/2025 15:35','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) it has been reported that the signals do not appear to be operating correctly and that only 4 vehicles are able to manoeuvr','-','-','See comments','54.94568868993313','-1.676119545179802'],
['F-006158','4048 - Wellington Street / Link Road - Gateshead','Gateshead','10/12/2025 07:05','TemporaryClear','Cycle Signal Issue','Other Cycle signal head for West St NB requires re-aligning 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','-','TS80 - Other Known Faults;','See comments','54.96471736870128','-1.604847482240757'],
['F-006138','4031 - Durham Road / Komatsu Access - Gateshead','Gateshead','03/12/2025 14:55','TemporaryClear','Other / Needs Review','Other If you are turning right onto harras bank from durham road the lights dont not allowed enough time, the priority is for the wrong side. Plus there are no lights on the other side of the juncti','-','TS80 - Other Known Faults;','See comments','54.89229859732005','-1.577173749511786'],
['F-006132','4073 - Prince Consort Road / Shipcote Lane - Gateshead','Gateshead','01/12/2025 13:45','TemporaryClear','Check / Adjust Timings','Other issue with the timings 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','-','TS80 - Other Known Faults;','See comments','54.95084360957183','-1.600699816795327'],
['F-006549','4033 - Eighton Lodge Roundabout - Gateshead','Gateshead','02/04/2026 09:55','TemporaryClear','Other / Needs Review','All lights across three lanes covered up on slip road and roundabout towards A1 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.91131651820577','-1.584440667844262'],
['F-006541','4130 - Northside Merge - Gateshead','Gateshead','30/03/2026 19:05','TemporaryClear','Lamp / LED Replacement','One traffic light damaged, another pointing the wrong way, lots of lamps out 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','','See comments','54.90586523841095','-1.561929040371169'],
['F-003421','0403 - Queen Victoria Road / Great North Children\'s Hospital','Newcastle','23/03/2026 07:21','TemporaryClear','Lamp / LED Replacement','Veh Phase D all RAG aspects and Veh Phase F Green. Plus+ Nodes faulty Pole 4 Phase D (4LLCSD) and Pole 11 Phase F (11LLCSF).','','TSA - Vehicle Amber Lamp Out;TSG - Vehicle Green Lamp Out;TSR - Vehicle Red Lamp Out;','See comments','54.97914787284022','-1.617445192339905'],
['F-003405','0019 - Heaton Road / Stephenson Road','Newcastle','16/03/2026 22:55','TemporaryClear','Other / Needs Review','The traffic lights do not stay green long enough for vehicles approaching the junction from Newton Road, which causes long queues to build up. Conversely, the lights often remain green longer than necessary for traffic coming from Heaton Road, even when there are no vehicles approaching the junction. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.99115274474461','-1.583705985780128'],
['F-003404','0352 - Scotswood Road / Refuse Access','Newcastle','16/03/2026 14:55','TemporaryClear','Other / Needs Review','Reported this before leaving but nothing done with it, no high speed detection fitted, pedestrian crossings area across a high speed road with no safe method of control currently in operation meaning it is unsafe and does not meet DfT standards. It-s the same at the Cow Hill junction 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.96581148990434','-1.667765291082972'],
['F-003357','0240 - Chillingham Road / Warton Terrace','Newcastle','08/03/2026 17:35','TemporaryClear','Other / Needs Review','Pedestrian call buttons do not light up when pressed. Both units on either side of road. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','TS80 - Other Known Faults;','See comments','54.98718720072976','-1.577215988068019'],
['F-003347','0301 - Denton Road / Whitfield Road','Newcastle','05/03/2026 18:25','TemporaryClear','Investigate All Out','Crossing fully out. 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments','54.97078246288688','-1.691960723819349'],
['F-003314','0049 - West Road / Condercum Road','Newcastle','03/03/2026 15:45','TemporaryClear','Push Button / Pedestrian','Sound functionality not working for the lights on the junction of Condercum Road and West Road 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.97643882149922','-1.658684954962752'],
['F-003302','0042 - Denton Hotel / Silver Lonnen','Newcastle','28/02/2026 16:05','TemporaryClear','Other / Needs Review','1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.98294527029368','-1.68372573388514'],
['F-003288','0264 - Freeman Road / Hospital','Newcastle','26/02/2026 19:35','TemporaryClear','Inspect / Survey','Traffic lights out altogether at busy crossing outside Freeman hospital next to bus stop on the side of the park 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','TSI - Site Inspection;','See comments','55.00110111662771','-1.595739086886312'],
['F-003284','0236 - Welback Road / Monkchester Road','Newcastle','26/02/2026 09:52','TemporaryClear','Investigate All Out','reports of a controller has been pushed over and wires exposed','','TS20 - Signals All Out;','See comments','54.97391024003824','-1.562836104958066'],
['F-003282','0273 - Chillingham Road / Hartford Street','Newcastle','24/02/2026 21:15','TemporaryClear','Realign Signal Head','The first set of lights on the southbound side is turned round 90 degrees 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','54.98326569590128','-1.575740177020137'],
['F-003239','0129 - Scotswood Road / Business Park','Newcastle','20/02/2026 11:15','TemporaryClear','Inspect / Survey','could this site be checked following 2 RTA this week causing near misses','','TSI - Site Inspection;','See comments','54.96457299894162','-1.656465217513983'],
['F-003233','0271 - Stamfordham Road / Westward Court','Newcastle','20/02/2026 08:34','TemporaryClear','Lamp / LED Replacement','PED LIGHT OUT AND SIGNALS HEAD OUT','','TSWL - Pedestrian Wait Lamp Out;','See comments','55.00128948363137','-1.697604359933664'],
['F-003229','0012 - Pilgrim Street / Blackett Street','Newcastle','18/02/2026 09:25','TemporaryClear','Push Button / Pedestrian','Green man button stuck on and also more time is now given to the pilgrim street side now for some reason. Also my previous report reference number 67906634 has still not been  fixed from barrack road bottom of stanhope street from 10th feb 1 Item Traffic Signals \ NON-URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - OUT OF SEQUENCE','','TS80 - Other Known Faults;','See comments','54.97423230618864','-1.611742915602861'],
['F-003216','0059 - Westgate Road / WCR','Newcastle','15/02/2026 08:25','TemporaryClear','Investigate All Out','All lights out 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments','54.97080311615258','-1.622710429218401'],
['F-003214','0286 - Rye Hill / Houston Street','Newcastle','14/02/2026 04:25','TemporaryClear','Post-RTC Repair','Temporarily traffic lights at the bottom of rye hill roundabout causing issues on houston street and many near miss collisions people trying to exit from Houston street where the primary school is. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.96940416993837','-1.630627791863305'],
['F-003208','0308 - Durant Road / College Street','Newcastle','13/02/2026 11:07','TemporaryClear','Post-RTC Repair','rtc','','TS59 - Signal Heads RTA/Damaged;','See comments','54.97597350936918','-1.608982650644848'],
['F-003201','0401 - Barras Bridge / Kings Walk','Newcastle','12/02/2026 08:05','TemporaryClear','Comms / Router Reboot','Proroute router is not responding. Please power cycle off and on again to reboot.','','TS72 - No Comms with OTU;','See comments','54.97853991930049','-1.613553190050973'],
['F-003193','0388 - Neville Street / Bewick Street','Newcastle','10/02/2026 16:15','TemporaryClear','Post-RTC Repair','post leaning 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','54.96903882113498','-1.617996208165132'],
['F-003191','0042 - Denton Hotel / Silver Lonnen','Newcastle','09/02/2026 16:05','TemporaryClear','Post-RTC Repair','Pedestrian element of this traffic signal has been smashed no green man for a number of months and needs replacing. Hard to establish if its safe to cross especially when busy traffic. 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','54.98294527029368','-1.68372573388514'],
['F-003184','0227 - Stanhope Street / Beaconsfield Street','Newcastle','08/02/2026 18:15','TemporaryClear','Lamp / LED Replacement','Other lights seem to be misaligned 1 Item Traffic Signals \ URGENT RESPONSE - SINGLE LIGHT OUT - RED','','TS80 - Other Known Faults;','See comments','54.97571841065774','-1.636927927430023'],
['F-003177','0135A - Walker Road / Pottery Bank','Newcastle','07/02/2026 07:46','TemporaryClear','Investigate All Out','this has been out since yesterday','','TS20 - Signals All Out;','See comments','54.96490024363274','-1.551875029825709'],
['F-003175','0172 - West Central Route / Gallowgate','Newcastle','06/02/2026 11:25','TemporaryClear','Check / Adjust Timings','No sink with this light and the one further ahead on barrack road. Customer mentions long wait to proceed on barrack coming form Strawberry ln 1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','','See comments','54.97406761601998','-1.622152486913561'],
['F-003173','0135A - Walker Road / Pottery Bank','Newcastle','06/02/2026 09:46','TemporaryClear','Investigate All Out','all out','','TS20 - Signals All Out;','See comments','54.96490024363274','-1.551875029825709'],
['F-003171','1446 - West Denton Way / Downham','Newcastle','06/02/2026 07:59','TemporaryClear','Check / Adjust Timings','various reports signal not operating properly - failing to change  sequence issues','','TS80 - Other Known Faults;','See comments','54.9926393125992','-1.692109397976964'],
['F-003162','0094 - Walker Road / Raby Street','Newcastle','04/02/2026 14:45','TemporaryClear','Other / Needs Review','Other Default setting wrong. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.96884985626661','-1.577208493550927'],
['F-003135','0050 - West Road / Copras Lane','Newcastle','02/02/2026 07:08','TemporaryClear','Comms / Router Reboot','5G Router offline, please power cycle off and on again to reboot','','TS72 - No Comms with OTU;','See comments','54.98418134968922','-1.690719824595647'],
['F-003131','1406 - Stamfordham Road / Hillhead Road','Newcastle','01/02/2026 19:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Detection fault or damage as Stamfordham Road green extends when no vehicles are present, adding unnecessary delay for the Hillhead Road approach 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','55.00287525011557','-1.702820760270868'],
['F-003121','0129 - Scotswood Road / Business Park','Newcastle','29/01/2026 18:25','TemporaryClear','Lamp / LED Replacement','Multiple lights out Both green lights out for traffic heading East 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.96457299894162','-1.656465217513983'],
['F-003096','0275 - WCR / North West Radial','Newcastle','27/01/2026 00:05','TemporaryClear','Other / Needs Review','Signal damaged The light looks like a bus or lorry has hit it, the lights are hanging down. Graham from premier traffic management has reported in by telephone. He was carrying out a site check on A','','TS80 - Other Known Faults;','See comments','54.98860306242214','-1.638617439149371'],
['F-002969','0271 - Stamfordham Road / Westward Court','Newcastle','23/01/2026 11:05','TemporaryClear','Lamp / LED Replacement','Single light out 298 stamfordham road 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','55.00128948363137','-1.697604359933664'],
['F-002922','0038 - WCR / Stanhope Street','Newcastle','12/01/2026 17:15','TemporaryClear','Lamp / LED Replacement','Multiple lights out Traffic lights damaged as a result of a car hitting them 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.97578724888793','-1.62674044446193'],
['F-002902','0049 - West Road / Condercum Road','Newcastle','05/01/2026 11:55','TemporaryClear','Other / Needs Review','Other Good morning, I realise that the regional signals team doesn\'t deal with road markings at signal-controlled junctions but I couldn\'t find another way to report this issue. At the northern arm,','','','See comments','54.97643882149922','-1.658684954962752'],
['F-002895','0208 - Fenham Hall Drive / Wingrove Road','Newcastle','02/01/2026 08:35','TemporaryClear','Other / Needs Review','Signal damaged Pedestrian crossing request column taken out by vehicle impact. Bits of tarmac and vehicle on the crossing. Location where student was run over in 2018 so some community impact. Junct','','TS80 - Other Known Faults;','See comments','54.98409418633637','-1.648501138076497'],
['F-002893','0135A - Walker Road / Pottery Bank','Newcastle','31/12/2025 11:25','TemporaryClear','Investigate All Out','All lights out Not working. Crossing isn\'t working 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.96490024363274','-1.551875029825709'],
['F-002890','0038 - WCR / Stanhope Street','Newcastle','29/12/2025 23:35','TemporaryClear','Post-RTC Repair','Signal damaged Road traffic collision has damaged the lights at the junction of Barrick Road Blue Star Pub Police Control Room Incident log 10052025 Collar Number 4224','','','See comments','54.97578724888793','-1.62674044446193'],
['F-002887','0147 - Gosforth High Street / Little Bridge','Newcastle','29/12/2025 06:35','TemporaryClear','Push Button / Pedestrian','Signal not operating correctly (e.g. failing to change or out of sequence) The green man is not displayed for enough time for pedestrians to cross the road. The traffic lights often change before pe','','TS80 - Other Known Faults;','See comments','54.99954119024219','-1.61804900430441'],
['F-002859','0374 - Redheugh Bridge / Bridgehead','Newcastle','22/12/2025 12:35','TemporaryClear','Lamp / LED Replacement','Single light out Coming off Redheugh, northbound, upper high level set of lights out on the right. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGAT','','TS80 - Other Known Faults;','See comments','54.965932','-1.623614'],
['F-002857','0059 - Westgate Road / WCR','Newcastle','22/12/2025 12:35','TemporaryClear','Other / Needs Review','Signal damaged St James Boulevard northbound - right hand signal damaged and not working for straight ahead lanes 1 Item Traffic Signals \ TRAFFIC SIGNALS','','','See comments','54.97080311615258','-1.622710429218401'],
['F-002855','0129 - Scotswood Road / Business Park','Newcastle','21/12/2025 08:55','TemporaryClear','Investigate All Out','Multiple lights out all lights out at the junctions william armstrong road scotswood road officers on scene reported by police officer simon hayes 2566 1','','TS80 - Other Known Faults;','See comments','54.96457299894162','-1.656465217513983'],
['F-002814','0003 - Westgate Road / Clayton Street','Newcastle','08/12/2025 10:15','TemporaryClear','Comms / Router Reboot','Other UTC showing router is offline, needs rebooting or plugging in. Please ring UTMC office when on site. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT','','','See comments','54.97032735808483','-1.61902752303952'],
['F-002799','0007 - Grainger Street / Newgate Street','Newcastle','06/12/2025 10:35','TemporaryClear','Other / Needs Review','Signal damaged Pedestrian countdown light damaged and not working. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.97140244595449','-1.615001914359169'],
['F-002795','0130 - Scotswood Road / Vickers','Newcastle','05/12/2025 08:55','TemporaryClear','Other / Needs Review','Other outside of pearsons engineering there is a traffic light on pedestrian crossing with exposed wires hanging down onto the crossing reported by police 4033','','TS80 - Other Known Faults;','See comments','54.96758172411054','-1.684419905574293'],
['F-002780','0040 - Denton Road / Whickham View','Newcastle','02/12/2025 21:25','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) The pedestrian crossing is not working correctly. The signal comes on automatically on repeat without anyone pressing the b','','','See comments','54.97999503633064','-1.689564007932319'],
['F-002769','0309 - Kenton Lane / Drayton Road','Newcastle','30/11/2025 19:05','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) The signals used to only be triggered to change if someone was waiting to come out of Drayton Road however since roadworks','','TS95 - Slot Cutting Required;','See comments','55.00354238853591','-1.654292295965178'],
['F-002745','0356 - Kenton Lane / Kenton School','Newcastle','26/11/2025 11:15','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Pedestrian button stuck in constantly being pressed. Traffic lights also not operating correctly after road resurfacing. Ta','','TS61 - Detectors (Loops) RTA/Damaged;','See comments','55.00263045392721','-1.657286224654522'],
['F-002742','0314 - Brunton Lane / Tudor Way','Newcastle','25/11/2025 20:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Ever since the road was resurfaced the traffic lights stay red for upwards of 2 minutes for Tudor Way and does not stay gre','','TS95 - Slot Cutting Required;','See comments','55.01117365070851','-1.668044935689594'],
['F-002732','0059 - Westgate Road / WCR','Newcastle','24/11/2025 15:28','TemporaryClear','Post-RTC Repair','reported pole leading into the road police on site','','TS67 - RTA;','See comments','54.97080311615258','-1.622710429218401'],
['F-002729','0407 - Newgate Street / St. Andrew\'s Street','Newcastle','24/11/2025 09:25','TemporaryClear','Comms / Router Reboot','Other UTC showing router is offline, needs rebooting. Please ring UTMC office when on site. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','54.97322914847265','-1.617609570637488'],
['F-002704','0019 - Heaton Road / Stephenson Road','Newcastle','19/11/2025 17:55','TemporaryClear','Loop / Slot Cutting','[Please check notes for rest of description]Other In the evening lights only allow two cars throughbefore changing yet lights for cars from Heaton Road allows much longer green. Results in unfair wa','','TS95 - Slot Cutting Required;','See comments','54.99115274474461','-1.583705985780128'],
['F-002703','0147 - Gosforth High Street / Little Bridge','Newcastle','19/11/2025 11:11','TemporaryClear','Check / Adjust Timings','request a review of the traffic lights. For pedestrians, the lights change from green to red too quickly for even non-disabled adults to cross, and for children, the elderly, disabled or pregnant people, there is not enough time to safely cross the full road before cars start moving again. note the lights seem to be on a longer setting during non-peak times, if this setting could be maintained throughout the day it would make the road significantly safer for pedestrians.','','TS39 - Timing Errors;','See comments','54.99954119024219','-1.61804900430441'],
['F-002686','0275 - WCR / North West Radial','Newcastle','18/11/2025 13:14','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TSA - Vehicle Amber Lamp Out;','See comments','54.98860306242214','-1.638617439149371'],
['F-002685','0248 - City Road / Milk Market','Newcastle','18/11/2025 13:14','TemporaryClear','Post-RTC Repair','Identified lamp fault','','TS62 - Signal Poles RTA/Damaged;','See comments','54.97058198544514','-1.601662092827126'],
['F-002680','0037 - Sandyford Road / Portland Terrace','Newcastle','18/11/2025 12:59','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TS10 - Single Lamp Out;','See comments','54.9811899322963','-1.601987008608304'],
['F-002675','0080 - John Dobson Street / St. Mary\'s Place','Newcastle','18/11/2025 09:12','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TS10 - Single Lamp Out;','See comments','54.9778436408367','-1.611453537874524'],
['F-002673','0056 - Neville Street / Clayton Street','Newcastle','18/11/2025 09:11','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TSR - Vehicle Red Lamp Out;','See comments','54.96853413804505','-1.620195802941396'],
['F-003440','0014 - Pilgrim Street / Market Street','Newcastle','29/03/2026 16:25','TemporaryClear','Check / Adjust Timings','The cycle lights on the north end of the Pilgrim Street cycle lane don\'t ever change to green and the beg button appears to be broken. I waited almost 4 minutes and three cycles of the main lights and there was no change, making it very difficult to cross the junction. 1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','','See comments','54.97317782591317','-1.611143836791825'],
['F-003439','1025 - Great Park Spine Road / Pegasus','Newcastle','28/03/2026 19:35','TemporaryClear','Cable / Electrical','traffic light signal damaged, wires exposed 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','55.02658998017394','-1.663270352560112'],
['F-003438','0262 - Armstrong Road / Clara Street','Newcastle','28/03/2026 13:25','TemporaryClear','Other / Needs Review','1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','54.9686842451562','-1.658284913569275'],
['F-001874','3309 - Holystone Bypass / Dual Toucan','North Tyneside','22/03/2026 05:55','TemporaryClear','Other / Needs Review','Traffic light hit be car torn off exposed wires 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','55.0228885563206','-1.533550850130922'],
['F-001867','2842 - Silverlink / Mallard Way','North Tyneside','16/03/2026 08:43','TemporaryClear','Check / Adjust Timings','Extreme delays at silverpoint car park near next/wren/hobbycraft. Traffic lights only allow 2/3 cars out at a time causing congestion and delays up to 45 mins to exit the car park. This has been a an ongoing problem for several months now without being resolved.','','TSS - General Survey/Timings;','See comments','55.01074222838038','-1.497292443589959'],
['F-001859','2062 - Shields Road / Foxhunters Road','North Tyneside','12/03/2026 23:45','TemporaryClear','Post-RTC Repair','Outside Aldi. A car has hit the light pole. Glass and plastic on pavement and road. 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','55.03417517768356','-1.457231656695739'],
['F-001832','3296 - Earsdon Road / Red Lion Pub','North Tyneside','03/03/2026 08:55','TemporaryClear','Inspect / Survey','The pedestrian crossing isn-t working on either side. School kids use this to get across the busy road. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','TSI - Site Inspection;','See comments','55.04504810079434','-1.495841621967173'],
['F-001794','3662 - Westmoor Roundabout','North Tyneside','19/02/2026 13:22','TemporaryClear','Post-RTC Repair','found while attending 3661','','TS59 - Signal Heads RTA/Damaged;','See comments','55.0254532973261','-1.586538570473017'],
['F-001748','3263 - Great Lime Road / Killingworth Road','North Tyneside','10/02/2026 16:15','TemporaryClear','Post-RTC Repair','post leaning 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','55.02625321503341','-1.560287308591512'],
['F-001679','2060 - Coast Road / Billy Mill','North Tyneside','27/01/2026 13:45','TemporaryClear','Realign Signal Head','Signal damaged Green light (nearest to Tesco-s) broken and facing wrong direction. Traffic light still operational. 1 Item Traffic Signals \ TRAFFIC SIGN','','TS80 - Other Known Faults;','See comments','55.01531638408018','-1.469002068812213'],
['F-001643','3300 - Great Lime Road / Palmersville','North Tyneside','23/01/2026 21:55','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Faulty traffic signals. Stopping traffic for no reason when there is no traffic to stop for.','','TS80 - Other Known Faults;','See comments','55.02434804760031','-1.543344579438283'],
['F-001585','2045 - Shiremoor Bypass / Grey Horse Pegasus','North Tyneside','02/01/2026 07:55','TemporaryClear','Post-RTC Repair','Other Pole 9 There is a fault on the ANPR cable somewhere between the controller and the top of the pole causing the voltage to be pulled down from 48VDC to 25VDC with load on and 35VDC with no load','','TS80 - Other Known Faults;','See comments','55.04108247693062','-1.504853919069277'],
['F-001584','3635 - A189 / Weetslade Roundabout','North Tyneside','01/01/2026 02:35','TemporaryClear','Post-RTC Repair','Other City security reported there has been a Road accident a street lamp has been hit and now traffic lights on the roundabout are completely off. 1 Item T','','TS80 - Other Known Faults;','See comments','55.03956105177819','-1.592420523834203'],
['F-001580','3260 - Salters Lane / West Farm Avenue','North Tyneside','30/12/2025 01:05','TemporaryClear','Post-RTC Repair','Signal damaged post leaning - may be a danger to pedestrians 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','55.01277452576708','-1.600684024971135'],
['F-001553','3260 - Salters Lane / West Farm Avenue','North Tyneside','21/12/2025 00:45','TemporaryClear','Post-RTC Repair','Signal damaged Northumbria police log NP-20251220-1258, relates to traffic lights at the junction of Salters lane and West Farm Avenue has been taken out, exposed wires','','','See comments','55.01277452576708','-1.600684024971135'],
['F-001539','3244 - Great Lime Road / Southgate','North Tyneside','15/12/2025 16:15','TemporaryClear','Post-RTC Repair','Signal damaged A tractor has knocked the lights over and wires exposed 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','55.02779937305292','-1.574989441627011'],
['F-001534','3300 - Great Lime Road / Palmersville','North Tyneside','15/12/2025 09:06','TemporaryClear','Check / Adjust Timings','Traffic light at junction of Great lime Road and Forest Gate appear to be causing traffic congestion. The flow of traffic is appalling. The lights only used to change when cars were exiting the estate as if on a sensor. They now change when there are no cars exiting. Not sure if building works/site is causing them to change more regularly than needed.','','TSS - General Survey/Timings;','See comments','55.02434804760031','-1.543344579438283'],
['F-001533','2062 - Shields Road / Foxhunters Road','North Tyneside','15/12/2025 08:35','TemporaryClear','Other / Needs Review','Other Lights missing from one side of street 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','55.03417517768356','-1.457231656695739'],
['F-001495','2432 - Beach Road / Preston Road North','North Tyneside','05/12/2025 09:55','TemporaryClear','Push Button / Pedestrian','There is currently a fault with the staggered signalised crossing on Preston North Road, near its roundabout with Beach Road. We have had a report that the push button unit has fallen away from the post and is hanging by the wires.','','TS55 - Faulty Push Buttons;','See comments','55.0234212592245','-1.454054265321432'],
['F-001492','3251-EAST - A19 / Holystone Roundabout East','North Tyneside','04/12/2025 12:05','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Waited on New York road, for around 8 minutes as lights o to roundabout did not change','','TS80 - Other Known Faults;','See comments','55.02857218489997','-1.523055585018341'],
['F-001482','3268 - Whitley Road / Station Road','North Tyneside','27/11/2025 20:35','TemporaryClear','Loop / Slot Cutting','Other Activation button on pedestrian crossing is stuck as pushed in all the time, it triggers green light for pedestrians even nobody is there. I couldn\'t release it, it\'s stuck. It builds up traff','','TS95 - Slot Cutting Required;','See comments','55.01120324983164','-1.566022131995374'],
['F-001457','3251-EAST - A19 / Holystone Roundabout East','North Tyneside','20/11/2025 12:25','TemporaryClear','Lamp / LED Replacement','Single light out Single red light out for holystone roundabout when going southbound 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','-','TS80 - Other Known Faults;','See comments','55.02857218489997','-1.523055585018341'],
['F-001455','3659 - Lockey Park / Wideopen','North Tyneside','20/11/2025 06:45','TemporaryClear','Post-RTC Repair','Signal damaged Traffic signal on junction of Great North Road Havannah Drive, NE13 6LD has been knocked over by a car and is currently in the middle of the road Police rang this in. Log number LatLo','-','TS80 - Other Known Faults;','See comments','55.04481557900528','-1.622120631861315'],
['F-001453','3268 - Whitley Road / Station Road','North Tyneside','19/11/2025 16:35','TemporaryClear','Check / Adjust Timings','[Please check notes for rest of description]Other The sequence appears to have been rephased so that, on occasion, traffic on Whitley Road going West to East contiunues with a green light whilst the','-','TS80 - Other Known Faults;','See comments','55.01120324983164','-1.566022131995374'],
['F-001429','2045 - Shiremoor Bypass / Grey Horse Pegasus','North Tyneside','17/11/2025 07:45','TemporaryClear','Post-RTC Repair','Other Pole 9.  Fault on the cable for ANPR camera somewhere between the controller and the top of the pole causing the voltage to be pulled down from 48VDC to 25VDC with load on and 35VDC with no lo','-','TS80 - Other Known Faults;','See comments','55.04108247693062','-1.504853919069277'],
['F-001920','2842 - Silverlink / Mallard Way','North Tyneside','08/04/2026 10:15','TemporaryClear','Post-RTC Repair','traffic lights been knocked down in a police pursuit on roundabout near silverlink sliproad leading onto A 19 nearest postcode NE29 7TE 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','55.01074222838038','-1.497292443589959'],
['F-001917','3268A - Station Road / Percy Hedley','North Tyneside','06/04/2026 03:05','TemporaryClear','Lamp / LED Replacement','traffic light is on the road 1 Item Traffic Signals \ URGENT RESPONSE - MULTIPLE LIGHTS OUT','','','See comments','55.01695103270049','-1.567509147876621'],
['F-001916','2035 - New York Way / Middle Engine Lane','North Tyneside','05/04/2026 18:55','TemporaryClear','Investigate All Out','1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments','55.01889811750141','-1.49497287768007'],
['F-001890','3319 - Killingworth Road / Hollywood Avenue','North Tyneside','28/03/2026 09:55','TemporaryClear','Post-RTC Repair','signal damaged after RTC - wires exposed on pedestrian button press - reported by pc Laurie parker 5193 - police ref 178 28022026 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','55.0104917493672','-1.600637437058098'],
['F-001889','3251-WEST - A19 / Holystone Roundabout West','North Tyneside','27/03/2026 16:15','TemporaryClear','Check / Adjust Timings','lights out of sequence, causing traffic to back up 1 Item Traffic Signals \ NON-URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - OUT OF SEQUENCE','','','See comments','55.02857218489997','-1.523055585018341'],
['F-001884','3294 - Forest Hall Road / Delaval Road','North Tyneside','25/03/2026 09:25','TemporaryClear','Post-RTC Repair','The pedestrian crossing button unit is hanging off the pole on one side of the road, with wires exposed.  It has been like this for months, but had been taped on - now someone has ripped the tape off 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','55.02358408557043','-1.561454388747648'],
['F-001425','BV08 - Cowpen Road / Asda - Blyth Valley','Northumberland','23/02/2026 14:54','TemporaryClear','Post-RTC Repair','Exposed wires due to rotten pole, enough for kids to get their hands in','-','TS80 - Other Known Faults;','See comments','55.12795863418429','-1.555150557435823'],
['F-001422','BV71 - Bridge Street / Quay Road','Northumberland','17/02/2026 10:48','TemporaryClear','Investigate All Out','The signals are not working at all','-','TS20 - Signals All Out;','See comments','','55.128010','-1.504723'],
['F-001419','W46 - North Seaton Road / Newbiggin Road','Northumberland','16/02/2026 15:04','TemporaryClear','Investigate All Out','site has been out since before xmas but nothing in the comments as to why','-','TS20 - Signals All Out;','See comments','55.1697987608843','-1.564156737683676'],
['F-001393','BV23 - Low Main Place / Dudley Lane - Blyth Valley','Northumberland','06/02/2026 08:29','TemporaryClear','Investigate All Out','All pedestrian and vehicle lights out','-','TS20 - Signals All Out;','See comments','55.08586905630486','-1.585625187009654'],
['F-001345','BV71 - Bridge Street / Quay Road','Northumberland','14/01/2026 16:46','TemporaryClear','Investigate All Out','SIGNALS STUCK ON RED','-','TS30 - Sticking on Red;','See comments','','55.128010','-1.504723'],
['F-001327','BV47 - Front Street / Lamb Street - Blyth Valley','Northumberland','03/01/2026 00:15','TemporaryClear','Post-RTC Repair','Signal damaged Signal damaged in RTC. Exposed wires and signal laying on the ground. Police log 1008-02.01.26 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT','','TS80 - Other Known Faults;','See comments','55.07886280397276','-1.570382682593018'],
['F-001320','A3 - Bondgate Without / Playhouse - Alnwick','Northumberland','30/12/2025 19:08','TemporaryClear','Investigate All Out','All out','','TS20 - Signals All Out;','See comments','55.41236617250188','-1.703178393268388'],
['F-001316','BV18 - Cowpen Road / Tynedale Drive - Blyth Valley','Northumberland','26/12/2025 20:06','TemporaryClear','Cable / Electrical','RTC - Traffic lights have been damaged  Wires exposed','','TS64 - Cables RTA/Damaged;','See comments','55.1299184014817','-1.54925327324807'],
['F-001314','BV31A - A1171 Dudley Lane / Cramlington N/BD - Blyth Valley','Northumberland','23/12/2025 13:12','TemporaryClear','Loop / Slot Cutting','Delay in signal for pedestrians to cross','','TS52 - Not Demanding (or Extending);','See comments','55.07143752410472','-1.590192655648519'],
['F-001294','BV02 - Bridge Street / Union Street - Blyth Valley','Northumberland','08/12/2025 10:17','TemporaryClear','Investigate All Out','the site number isn\'t on imtrac the site is Quay Road/Bridge Street Blyth','','TS20 - Signals All Out;','See comments','55.12718056923095','-1.507759905190653'],
['F-001477','BV20 - Main Street / Seghill - Blyth Valley','Northumberland','07/04/2026 07:52','TemporaryClear','Post-RTC Repair','Reported that head is twisted due to vehicle impact','','TS59 - Signal Heads RTA/Damaged;','See comments','55.06310429136482','-1.551332644689765'],
['F-001470','BV10 - Rotary Way / Amersham Road - Blyth Valley','Northumberland','01/04/2026 09:06','TemporaryClear','Investigate All Out','ref 9222646','','TS20 - Signals All Out;','See comments','55.11524600692549','-1.506979350536952'],
['F-001223','6505 - Front Street / East Street','South Tyneside','14/03/2026 11:15','TemporaryClear','Check / Adjust Timings','1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','TS80 - Other Known Faults;','See comments','54.94970387669774','-1.364426026332893'],
['F-001211','6060 - Sunderland Road / Grosvenor Road','South Tyneside','06/03/2026 15:55','TemporaryClear','Check / Adjust Timings','Lights only allowing a few cars through before cycling to next direction 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.98443827412241','-1.42158383376416'],
['F-001190','6855 - Addison Road / Hylton Lane','South Tyneside','03/03/2026 05:45','TemporaryClear','Loop / Slot Cutting','Once upon a time the signal on Hylton Lane was Smart i.e. when you approached it on Red it would instantly turn to Green if there was no-one else around. I used to notice this as I was going through very early in the morning.  Can\'t be a coincidence but just after resurfacing works were carried out where everything was switched off and then put back on they stopped being Smart and now you are kept waiting for ages. Can they be put back. 1 Item Traffic Signals \ NON-URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - OUT OF SEQUENCE','','TS80 - Other Known Faults;','See comments','54.94264449290639','-1.453826680702889'],
['F-001121','7505 - Albert Road / Park Road','South Tyneside','03/02/2026 13:25','TemporaryClear','Lamp / LED Replacement','Multiple lights out traffic lights out 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','54.97845135979095','-1.495964012156776'],
['F-001109','6858 - A194 / Mill Lane','South Tyneside','30/01/2026 09:45','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Heading south the pegasus crossing constantly activate when nothing crossing. It seems to be faulty. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.95219359970541','-1.506060430266275'],
['F-001105','6060 - Sunderland Road / Grosvenor Road','South Tyneside','29/01/2026 07:45','TemporaryClear','Investigate All Out','All lights out only green light working 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.98443827412241','-1.42158383376416'],
['F-001077','6060 - Sunderland Road / Grosvenor Road','South Tyneside','26/01/2026 08:35','TemporaryClear','Lamp / LED Replacement','Single light out light stuck on red 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments','54.98443827412241','-1.42158383376416'],
['F-000983','6058 - John Reid Road / Fire Station','South Tyneside','22/12/2025 09:55','TemporaryClear','Investigate All Out','All lights out 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments','54.96930454188153','-1.427242755004158'],
['F-000976','6505 - Front Street / East Street','South Tyneside','18/12/2025 15:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) As you turn left at the lights to go toward seafront the lights are only letting 3,4 car\'s out and the traffic is building','','TS61 - Detectors (Loops) RTA/Damaged;','See comments','54.94970387669774','-1.364426026332893'],
['F-000975','7517 - Victoria Road / Mill Lane','South Tyneside','18/12/2025 14:54','TemporaryClear','Check / Adjust Timings','this fault has been reported at  14:21 today after the initial was investigate at 08:45, not sure if timings have been changed and this is why this is causing so much congestion now. i have video footage of the timing issues here, who ever is responding to this fault let me know and i can share it with you.','','TS39 - Timing Errors;','See comments','54.96115759481308','-1.525712245547283'],
['F-000973','7517 - Victoria Road / Mill Lane','South Tyneside','18/12/2025 08:45','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Lights only remaining on green for a very short period and then going back to red','','','See comments','54.96115759481308','-1.525712245547283'],
['F-000972','7510 - Victoria Road / Station Road','South Tyneside','18/12/2025 08:05','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) customer reports that the lights are out of sync and are only letting 2 to 3 cars through at a time, so there are large que','','TS61 - Detectors (Loops) RTA/Damaged;','See comments','54.97258467510753','-1.51719081106566'],
['F-000971','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 23:15','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Traffic travelling on Victoria Road west only have 4-5sec on green considerable tail backs.','','TS61 - Detectors (Loops) RTA/Damaged;','See comments','54.96115759481308','-1.525712245547283'],
['F-000968','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 11:15','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Traffic lights at the junction of Mill Lane and Victoria Road West are only letting 4 cars through there is a large tail ba','','TS61 - Detectors (Loops) RTA/Damaged;','See comments','54.96115759481308','-1.525712245547283'],
['F-000966','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 09:15','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Lights have been put back on after work was completed but are out of sync and only letting a couple cars through causing ta','','','See comments','54.96115759481308','-1.525712245547283'],
['F-000965','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 08:55','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Thee is a job already reported on november 26 for this same Signal. The customer says that the light when going north east','','TS50 - Detection Faults;','See comments','54.96115759481308','-1.525712245547283'],
['F-000962','6861(EAST) - A19/A1290 / East Side of Roundabout','South Tyneside','13/12/2025 02:35','TemporaryClear','Post-RTC Repair','Signal damaged Car hit traffic light in road traffic accident caller not certain if it is this selected traffic signal or the one next to it Site No 6861E A19 A1290 East South Tyneside Tyne and Wear','','','See comments','',''],
['F-000915','7517 - Victoria Road / Mill Lane','South Tyneside','26/11/2025 09:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) the timing are out on these lights as there is only a few car\'s getting through 1','','TS61 - Detectors (Loops) RTA/Damaged;','See comments','54.96115759481308','-1.525712245547283'],
['F-000870','6047 - John Reid Road / Perth Avenue','South Tyneside','18/11/2025 09:47','TemporaryClear','Lamp / LED Replacement','1st Level Red Lamp Fault','','TSR - Vehicle Red Lamp Out;','See comments','54.96547215933422','-1.459652555368664'],
['F-001244','7205 - Newcastle Road / Jarrow Road','South Tyneside','27/03/2026 12:13','TemporaryClear','Comms / Router Reboot','5G Proroute router has stopped responding. Please try to power down the router and then power up to trigger a reboot.','','TS70 - OTU General Fault;','See comments','54.97651155467308','-1.451075479444328'],
['F-006208','8106 - Wheatsheaf Gyratory - Sunderland','Sunderland','16/03/2026 21:25','TemporaryClear','Other / Needs Review','The controller green box door is on the ground next to the controller box all wires are showing it is next to the pelican crossing on Newcastle Road further down from Tesco Extra. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.91573892574754','-1.382675854709159'],
['F-006192','9630E - A1231 Part Time Signals / A182  - Sunderland','Sunderland','09/03/2026 07:20','TemporaryClear','Investigate All Out','UTC showing All Lamps Off','','TS20 - Signals All Out;','See comments','54.90189596665666','-1.537646161234519'],
['F-006116','8026 - Harbour View / Roker Terrace','Sunderland','19/02/2026 09:57','TemporaryClear','Inspect / Survey','Pole 9 has a lot of movement within the NAL socket and needs adjusting slighting this was very noticeable on 18/02/2026 due to the high winds.','','TSI - Site Inspection;','See comments','','54.92142364234266','-1.3657694731517276'],
['F-006093','8606 - Silksworth Terrace / Blind Lane - Sunderland','Sunderland','11/02/2026 09:45','TemporaryClear','Check / Adjust Timings','not changing for the pedestrians but changes for cars please investigate thank you 1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','','See comments','54.87286098601084','-1.400396260261218'],
['F-005940','8020 - Clockwell Street / Northern Way - Sunderland','Sunderland','19/01/2026 10:33','TemporaryClear','Comms / Router Reboot','router offline, please reboot.','','TS70 - OTU General Fault;','See comments','54.91810169046015','-1.406435672426071'],
['F-005925','8006 - Newcastle Road / Thompson Road - Sunderland','Sunderland','06/01/2026 22:05','TemporaryClear','Post-RTC Repair','Signal damaged Police have called to report the signal damaged after a vehicle hit it wires are sparking Police log-NP 20260106 0968--Police collar 9138 Traffic light is on the A1018 flyover on Newc','','TS80 - Other Known Faults;','See comments','54.92724939005708','-1.389051972297423'],
['F-005923','8152 - West Wear Street / William Street - Sunderland','Sunderland','06/01/2026 10:16','TemporaryClear','Lamp / LED Replacement','needs new head - seen by telent','','TS80 - Other Known Faults;','See comments','54.90897750339284','-1.379046664650161'],
['F-005919','8184 - Hillthorn Park / Nissan Way - Sunderland','Sunderland','03/01/2026 14:05','TemporaryClear','Post-RTC Repair','Signal damaged traffic light infiniti drive junction with nissan way knocked over as a result of single vehicle rtc, traffic light completely felled onto the path','','TS80 - Other Known Faults;','See comments','54.91023612435198','-1.487196719342235'],
['F-005918','8204 - Whitburn Road / Seaburn Park','Sunderland','01/01/2026 08:45','TemporaryClear','Post-RTC Repair','Signal damaged lights works but pole bent following an RTC - police log 1149 31122025 - rang through by Sunderland Council 1 Item Traffic Signals \ TRAFFI','','TS80 - Other Known Faults;','See comments','54.93215501357841','-1.367730969923343'],
['F-005898','9615 - A1290 / Cherry Blossom Way','Sunderland','22/12/2025 06:55','TemporaryClear','Check / Adjust Timings','[Please check notes for rest of description]Signal not operating correctly (e.g. failing to change or out of sequence) I also noticed this issue on Friday. The lights on A1290 are only staying on gr','','','See comments','54.91867424427944','-1.48904419137731'],
['F-005748','8024 - A19 / A1231 Part Time Signals - Sunderland','Sunderland','04/11/2025 19:58','TemporaryClear','Lamp / LED Replacement','Please select an option that best describes the problem: Single light out Green lights are not working on two sets of lights Green light not on Site 8024 A19  A1231 Sunderland Tyne  Wear as the location of the issue','','TS80 - Other Known Faults;','See comments','54.90906330606413','-1.460986663478195'],
['F-006270','8076 - Washington Road / Ferryboat Lane','Sunderland','07/04/2026 22:55','TemporaryClear','Post-RTC Repair','Traffic light hit in road traffic accident wires exposed police log 1214.07042026 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.92575475538066','-1.4610780031353'],
['F-006265','8622 - Doxford Park Way E/B + W/B / West Bound - Sunderland','Sunderland','07/04/2026 09:25','TemporaryClear','Investigate All Out','Traffic lights are off again, when lights are fixed they only work for a couple of hours and then go off again. Lights need looked at properly as pedestrain crossing lights are not working correctly. Concerns over if lights don\'t get fixed, someone will get hurt when crossing the road due to amount of cars speeding when lights are out. 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments','54.86559084569708','-1.416317385053593'],
['F-006257','8014 - North Bridge Street / Dame Dorothy Street - Sunderland','Sunderland','04/04/2026 09:26','TemporaryClear','Post-RTC Repair','Damaged traffic light - slightly bend to the side, due to road traffic collusion','','TS62 - Signal Poles RTA/Damaged;','See comments','54.91153341517217','-1.383242210058953'],
['F-006249','9289 - Pemberton Street / Front Street - Sunderland','Sunderland','01/04/2026 12:15','Open','Check / Adjust Timings','Residents have requested that a traffic light survey be carried out, as the traffic signals in Hetton are causing significant tailbacks during peak times. This is particularly noticeable at the pedestrian crossing outside Tesco, where the signals appear to change almost immediately after traffic begins to move. As a result, vehicles are frequently being stopped, which prevents a smooth flow of traffic and leads to congestion in the area. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.82121536682462','-1.452221798101647'],
['F-006248','8622 - Doxford Park Way E/B + W/B / West Bound - Sunderland','Sunderland','01/04/2026 11:05','TemporaryClear','Investigate All Out','all lights off since last Thursday 26.03.26 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','TS20 - Signals All Out;','See comments','54.86559084569708','-1.416317385053593'],
['F-006243','9637 - Vigo Lane / Picktree Lane','Sunderland','30/03/2026 07:45','TemporaryClear','Loop / Slot Cutting','Since new paths were put in, the Vigo lane lights were only letting 3 cars out, that has improved however when they change for other cars where you very rarely have more than 2 cars sometimes none it-s on that way for a lot longer 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments','54.88283363537969','-1.55724654341731'],
['F-006240','9630E - A1231 Part Time Signals / A182  - Sunderland','Sunderland','28/03/2026 00:55','TemporaryClear','Post-RTC Repair','URGENT, Police rang to report a vehicle has crashed into traffic lights on Sunderland roundabout A182   A1231, there are exposed wires showing. log number 1190 27th march 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments','54.90189596665666','-1.537646161234519'],
['F-006234','8024 - A19 / A1231 Part Time Signals - Sunderland','Sunderland','25/03/2026 10:05','TemporaryClear','Lamp / LED Replacement','Two of the three traffic lights are not visible as turned away . if the only one left fails traffic joining from the A19 will have to treat the junction as a normal roundabout with resulting danger. 1 Item Traffic Signals \ URGENT RESPONSE - MULTIPLE LIGHTS OUT','','','See comments','54.90906330606413','-1.460986663478195'],
['F-006228','9292 - Easington Lane / Murton Lane - Sunderland','Sunderland','24/03/2026 15:16','TemporaryClear','Investigate All Out','All out','','TS80 - Other Known Faults;','See comments','54.80876124826359','-1.437502443923194']
];

const CAT_COLOURS = {
  'Post-RTC Repair': '#B91C1C',
  'Check / Adjust Timings': '#92400E',
  'Lamp / LED Replacement': '#D97706',
  'Investigate All Out': '#DC2626',
  'Comms / Router Reboot': '#2563EB',
  'Push Button / Pedestrian': '#7C3AED',
  'Loop / Slot Cutting': '#059669',
  'Cable / Electrical': '#EA580C',
  'Realign Signal Head': '#0891B2',
  'Detection (Above Ground)': '#4F46E5',
  'MOVA / SCOOT': '#6D28D9',
  'Controller / Cabinet': '#BE185D',
  'Cycle Signal Issue': '#0D9488',
  'Other / Needs Review': '#6B7280',
};

const CATEGORIES = [
  'Post-RTC Repair',
  'Check / Adjust Timings',
  'Lamp / LED Replacement',
  'Investigate All Out',
  'Comms / Router Reboot',
  'Push Button / Pedestrian',
  'Loop / Slot Cutting',
  'Cable / Electrical',
  'Realign Signal Head',
  'Detection (Above Ground)',
  'MOVA / SCOOT',
  'Controller / Cabinet',
  'Cycle Signal Issue',
  'Other / Needs Review'
];

// State management (localStorage + API)
let outcomes = {};  // key: "area|faultId" -> {status, notes}

function stateKey(area, faultId) { return area + '|' + faultId; }

function loadLocalState() {
  try {
    const s = localStorage.getItem('council_action_state');
    if (s) outcomes = JSON.parse(s);
  } catch(e) {}
}

function saveLocalState() {
  localStorage.setItem('council_action_state', JSON.stringify(outcomes));
}

function getOutcome(area, faultId) {
  return outcomes[stateKey(area, faultId)] || { status: 'pending', notes: '' };
}

function setOutcome(area, faultId, status, notes) {
  const key = stateKey(area, faultId);
  outcomes[key] = { status, notes };
  saveLocalState();
  syncToServer(area, faultId, status, notes);
}

async function syncToServer(area, faultId, status, notes) {
  try {
    await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'save', area, faultId, status, notes })
    });
  } catch(e) { console.warn('Sync failed:', e); }
}

async function loadFromServer() {
  try {
    const r = await fetch(API_URL);
    const data = await r.json();
    if (data.outcomes) {
      for (const o of data.outcomes) {
        const key = stateKey(o.area, o.fault_id);
        outcomes[key] = { status: o.status || 'pending', notes: o.notes || '' };
      }
      saveLocalState();
    }
  } catch(e) { console.warn('Load from server failed:', e); }
}

function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s || '');
  return d.innerHTML;
}


// Clean up raw IMTRAC text into something a technician can read at a glance
function cleanForTech(info, closeComments) {
  var text = info || closeComments || '';
  // Strip IMTRAC boilerplate
  text = text.replace(/Signal damaged\s*/i, '');
  text = text.replace(/Signal not operating correctly \(e\.g\. failing to change or out of sequence\)\s*/i, '');
  text = text.replace(/Please select an option that best describes the problem:\s*/i, '');
  text = text.replace(/\[Please check notes for rest of description\]\s*/i, '');
  text = text.replace(/Other\s+/i, '');
  // Strip LatLon
  text = text.replace(/LatLon:\S+/g, '');
  // Strip IMTRAC response category at the end
  text = text.replace(/\d+\s*Item Traffic Signals\s*\.*/g, '');
  // Strip police log/collar numbers but keep the fact police reported it
  text = text.replace(/Police log[\s-]*\S+/gi, '(police reported)');
  text = text.replace(/Police collar\s*\S+/gi, '');
  text = text.replace(/log number\s*\S+/gi, '');
  text = text.replace(/NP-\d+-\d+/g, '');
  text = text.replace(/collar\s*\d+/gi, '');
  // Clean up whitespace
  text = text.replace(/\s+/g, ' ').trim();
  // If nothing useful left, try close comments
  if (text.length < 5 && closeComments) {
    text = closeComments.replace(/\s+/g, ' ').trim();
  }
  return text;
}

let activeCat = ''; // empty = show all

function render() {
  const areaFilter = document.getElementById('areaFilter').value;
  const statusFilter = document.getElementById('statusFilter').value;
  const search = document.getElementById('searchInput').value.toLowerCase().trim();

  // Filter faults (area + status + search, before category filter)
  const preFiltered = FAULTS.filter(f => {
    if (areaFilter && f[2] !== areaFilter) return false;
    const o = getOutcome(f[2], f[0]);
    if (statusFilter && o.status !== statusFilter) return false;
    if (search) {
      const hay = (f[0] + ' ' + f[1] + ' ' + f[6] + ' ' + f[7] + ' ' + f[8]).toLowerCase();
      if (!hay.includes(search)) return false;
    }
    return true;
  });

  // Count per category (for button badges) — before category filter
  const catCounts = {};
  for (const cat of CATEGORIES) catCounts[cat] = 0;
  for (const f of preFiltered) catCounts[f[5]] = (catCounts[f[5]] || 0) + 1;

  // Build category filter buttons
  let btnHtml = '<button class="cat-btn' + (!activeCat ? ' active' : '') + '" data-cat="">All<span class="cat-btn-count">' + preFiltered.length + '</span></button>';
  for (const cat of CATEGORIES) {
    if (catCounts[cat] === 0) continue;
    const colour = CAT_COLOURS[cat] || '#6B7280';
    btnHtml += '<button class="cat-btn' + (activeCat === cat ? ' active' : '') + '" data-cat="' + esc(cat) + '" style="' + (activeCat === cat ? 'background:'+colour+';border-color:'+colour+';color:white' : '') + '">' + esc(cat) + '<span class="cat-btn-count">' + catCounts[cat] + '</span></button>';
  }
  document.getElementById('catFilters').innerHTML = btnHtml;

  // Apply category filter
  const filtered = activeCat ? preFiltered.filter(f => f[5] === activeCat) : preFiltered;

  // Stats
  const total = filtered.length;
  const pending = filtered.filter(f => getOutcome(f[2], f[0]).status === 'pending').length;
  const inProgress = filtered.filter(f => getOutcome(f[2], f[0]).status === 'in_progress').length;
  const completed = filtered.filter(f => getOutcome(f[2], f[0]).status === 'completed').length;

  document.getElementById('statsRow').innerHTML =
    '<div class="stat-card stat-total"><div class="stat-number">' + total + '</div><div class="stat-label">Showing</div></div>' +
    '<div class="stat-card stat-pending"><div class="stat-number">' + pending + '</div><div class="stat-label">Pending</div></div>' +
    '<div class="stat-card stat-progress"><div class="stat-number">' + inProgress + '</div><div class="stat-label">In progress</div></div>' +
    '<div class="stat-card stat-completed"><div class="stat-number">' + completed + '</div><div class="stat-label">Completed</div></div>';

  // Group by site (area + site name) — one card per location
  const siteGroups = {};
  const siteOrder = [];
  for (const f of filtered) {
    const siteKey = f[2] + '|' + f[1]; // area|site
    if (!siteGroups[siteKey]) {
      siteGroups[siteKey] = [];
      siteOrder.push(siteKey);
    }
    siteGroups[siteKey].push(f);
  }

  // Stats — count sites not individual faults
  const siteCount = siteOrder.length;
  const faultCount = filtered.length;
  const sitesCompleted = siteOrder.filter(k => {
    return siteGroups[k].every(f => getOutcome(f[2], f[0]).status === 'completed');
  }).length;
  const sitesInProgress = siteOrder.filter(k => {
    return siteGroups[k].some(f => getOutcome(f[2], f[0]).status === 'in_progress') &&
           !siteGroups[k].every(f => getOutcome(f[2], f[0]).status === 'completed');
  }).length;
  const sitesPending = siteCount - sitesCompleted - sitesInProgress;

  document.getElementById('statsRow').innerHTML =
    '<div class="stat-card stat-total"><div class="stat-number">' + siteCount + '</div><div class="stat-label">Sites (' + faultCount + ' faults)</div></div>' +
    '<div class="stat-card stat-pending"><div class="stat-number">' + sitesPending + '</div><div class="stat-label">Pending</div></div>' +
    '<div class="stat-card stat-progress"><div class="stat-number">' + sitesInProgress + '</div><div class="stat-label">In progress</div></div>' +
    '<div class="stat-card stat-completed"><div class="stat-number">' + sitesCompleted + '</div><div class="stat-label">Completed</div></div>';

  // Render grouped cards
  const container = document.getElementById('content');
  let html = '';

  if (siteOrder.length > 0) {
    html += '<div class="fault-list">';
    for (const siteKey of siteOrder) {
      const faults = siteGroups[siteKey];
      const first = faults[0]; // use first fault for site name/area
      const siteName = first[1];
      const area = first[2];
      const siteUid = (area + '__' + siteName).replace(/[^a-zA-Z0-9_]/g, '_');

      // Determine overall site status (worst status wins)
      const allCompleted = faults.every(f => getOutcome(f[2], f[0]).status === 'completed');
      const anyInProgress = faults.some(f => getOutcome(f[2], f[0]).status === 'in_progress');
      const anyEscalated = faults.some(f => getOutcome(f[2], f[0]).status === 'escalated');
      const siteStatusClass = allCompleted ? ' status-completed' : anyEscalated ? ' status-escalated' : anyInProgress ? ' status-in_progress' : '';

      // Collect unique fault codes and categories
      const codes = new Set();
      const cats = new Set();
      faults.forEach(f => {
        if (f[8] && f[8] !== '-' && f[8] !== 'None') codes.add(f[8]);
        cats.add(f[5]);
      });

      // Get notes for the site (use first fault's uid for site-level notes)
      const siteNoteKey = area + '__site__' + siteName.replace(/[^a-zA-Z0-9]/g, '_');
      const siteO = getOutcome(area, 'site__' + siteName.replace(/[^a-zA-Z0-9]/g, '_'));

      var isSelected = selectedSites.has(siteKey);
      html += '<div class="fault-card' + siteStatusClass + (isSelected ? ' selected' : '') + '" data-site-key="' + esc(siteKey) + '">';

      // Checkbox + Site name + fault count badge
      html += '<div class="fault-top">';
      html += '<input type="checkbox" class="site-checkbox" data-site-key="' + esc(siteKey) + '" ' + (isSelected ? 'checked' : '') + '>';
      var age = faultAge(first[3]);
      html += '<div><div class="fault-site">' + esc(siteName) + '</div>';
      html += '<div style="font-size:12px;color:var(--text-light);margin-top:2px">' + esc(area) + ' &middot; ' + faults.length + ' fault' + (faults.length !== 1 ? 's' : '') + (age.text ? '<span class="fault-age ' + age.cls + '">' + age.text + '</span>' : '') + '</div></div>';
      if (allCompleted) html += '<span class="status-pill completed">completed</span>';
      else if (anyEscalated) html += '<span class="status-pill escalated">escalated</span>';
      else if (anyInProgress) html += '<span class="status-pill in_progress">in progress</span>';
      else html += '<span class="status-pill pending">pending</span>';
      html += '</div>';

      // Show additional info from each fault (deduplicated)
      const infos = new Set();
      faults.forEach(f => { if (f[6] && f[6].length > 3) infos.add(f[6]); });
      if (infos.size > 0) {
        html += '<div class="fault-summary">';
        var infoArr = Array.from(infos);
        html += esc(infoArr[0]);
        if (infoArr.length > 1) html += '<div style="margin-top:4px;font-size:12px;color:var(--text-light)">+ ' + (infoArr.length - 1) + ' more report' + (infoArr.length > 2 ? 's' : '') + '</div>';
        html += '</div>';
      }

      // Fault codes
      if (codes.size > 0) html += '<div class="fault-codes">' + esc(Array.from(codes).join(' | ')) + '</div>';

      // Categories (if mixed)
      if (cats.size > 1) {
        html += '<div style="font-size:11px;color:var(--text-light);margin-bottom:8px">Categories: ' + esc(Array.from(cats).join(', ')) + '</div>';
      }

      // Site-level notes
      if (siteO.notes) {
        html += '<div class="fault-notes-display"><strong>Your notes</strong>' + esc(siteO.notes) + '</div>';
      }

      // Actions row
      html += '<div class="fault-footer">';
      // Status
      html += '<select data-area="'+esc(area)+'" data-site="'+esc(siteName)+'" data-fids="'+esc(faults.map(f=>f[0]).join(','))+'" data-action="site-status">';
      var currentStatus = allCompleted ? 'completed' : anyEscalated ? 'escalated' : anyInProgress ? 'in_progress' : 'pending';
      html += '<option value="pending"' + (currentStatus==='pending'?' selected':'') + '>Pending</option>';
      html += '<option value="in_progress"' + (currentStatus==='in_progress'?' selected':'') + '>In Progress</option>';
      html += '<option value="completed"' + (currentStatus==='completed'?' selected':'') + '>Completed</option>';
      html += '<option value="escalated"' + (currentStatus==='escalated'?' selected':'') + '>Escalated</option>';
      html += '</select>';
      // Recategorise
      var siteCat = Array.from(cats)[0] || '';
      html += '<select data-area="'+esc(area)+'" data-fids="'+esc(faults.map(f=>f[0]).join(','))+'" data-action="recat" style="font-size:11px">';
      for (var ci = 0; ci < CATEGORIES.length; ci++) {
        var c = CATEGORIES[ci];
        html += '<option value="'+esc(c)+'"' + (c === siteCat ? ' selected' : '') + '>' + esc(c) + '</option>';
      }
      html += '</select>';
      // Navigate button
      var navLat = first[10], navLon = first[11];
      if (navLat && navLon) {
        html += '<a class="btn-sm" href="https://www.google.com/maps/dir/?api=1&destination='+navLat+','+navLon+'" target="_blank" style="text-decoration:none;background:#1A7F4B;color:white;border-color:#1A7F4B">Navigate</a>';
      } else {
        html += '<a class="btn-sm" href="https://www.google.com/maps/search/?api=1&query='+encodeURIComponent(siteName + ' ' + area)+'" target="_blank" style="text-decoration:none">Search map</a>';
      }
      html += '<button class="btn-sm" data-area="'+esc(area)+'" data-site="'+esc(siteName)+'" data-action="toggle-site-note">' + (siteO.notes ? 'Edit note' : 'Add note') + '</button>';
      html += '<button class="btn-sm" data-site-uid="'+esc(siteUid)+'" data-action="toggle-site-detail">' + faults.length + ' fault' + (faults.length !== 1 ? 's' : '') + ' - details</button>';
      html += '</div>';

      // Site-level note editor
      var noteId = 'sitenote-' + siteUid;
      html += '<div class="note-input" id="'+noteId+'">';
      html += '<textarea placeholder="Add a note for this site...">' + esc(siteO.notes) + '</textarea>';
      html += '<button class="note-save" data-area="'+esc(area)+'" data-site="'+esc(siteName)+'" data-action="save-site-note" data-note-id="'+noteId+'">Save note</button>';
      html += '</div>';

      // Expandable detail: individual faults
      html += '<div class="fault-detail" id="sitedetail-'+esc(siteUid)+'">';
      for (const f of faults) {
        html += '<div style="padding:8px 0;border-bottom:1px solid #eee">';
        html += '<span><span class="detail-label">' + esc(f[0]) + '</span> - ' + esc(f[3]) + ' - ' + esc(f[4]) + '</span>';
        if (f[6]) html += '<span style="display:block;margin-top:4px">' + esc(f[6]) + '</span>';
        if (f[7] && f[7] !== 'None') html += '<span style="display:block;color:var(--accent);font-style:italic;margin-top:2px">Tech: ' + esc(f[7]) + '</span>';
        if (f[8] && f[8] !== '-' && f[8] !== 'None') html += '<span style="display:block;margin-top:2px;font-size:11px">Code: ' + esc(f[8]) + '</span>';
        html += '</div>';
      }
      html += '</div>';

      html += '</div>';
    }
    html += '</div>';
  }

  if (siteOrder.length === 0) {
    html = '<div class="empty">No faults match your filters.</div>';
  }

  container.innerHTML = html;

  // Update map — show only selected sites if any are selected, otherwise show all
  currentSiteGroups = siteGroups;
  currentSiteOrder = siteOrder;
  var mapOrder = selectedSites.size > 0 ? siteOrder.filter(k => selectedSites.has(k)) : siteOrder;
  updateMap(siteGroups, mapOrder);

  // Update selection count
  var selEl = document.getElementById('selCount');
  selEl.textContent = selectedSites.size > 0 ? '(' + selectedSites.size + ' selected)' : '';

  // Update print meta
  var aF = document.getElementById('areaFilter').value || 'All areas';
  document.getElementById('printMeta').textContent = aF + ' | ' + (activeCat || 'All categories') + ' | Printed ' + new Date().toLocaleDateString('en-GB');
}

// Event delegation — all clicks and changes handled here, no inline handlers
document.addEventListener('click', function(e) {
  // Route planner
  if (e.target.closest('[data-action="plan-route"]')) {
    planRoute(currentSiteGroups, currentSiteOrder);
    return;
  }

  // Print job sheet — if sites selected, hide unselected before printing
  if (e.target.closest('[data-action="print-jobs"]')) {
    if (selectedSites.size > 0) {
      document.querySelectorAll('.fault-card').forEach(function(card) {
        var key = card.dataset.siteKey;
        if (key && !selectedSites.has(key)) card.style.display = 'none';
      });
    }
    window.print();
    if (selectedSites.size > 0) {
      document.querySelectorAll('.fault-card').forEach(function(card) {
        card.style.display = '';
      });
    }
    return;
  }

  // Select all visible sites
  if (e.target.closest('[data-action="select-all"]')) {
    currentSiteOrder.forEach(function(k) { selectedSites.add(k); });
    render();
    return;
  }

  // Clear selection
  if (e.target.closest('[data-action="clear-selection"]')) {
    selectedSites.clear();
    render();
    return;
  }

  // Category filter button
  const catBtn = e.target.closest('.cat-btn');
  if (catBtn) {
    activeCat = catBtn.dataset.cat || '';
    render();
    return;
  }

  // Toggle note panel
  const noteBtn = e.target.closest('[data-action="toggle-note"]');
  if (noteBtn) {
    const el = document.getElementById('note-' + noteBtn.dataset.uid);
    if (el) el.classList.toggle('visible');
    return;
  }

  // Toggle site detail panel
  const detailBtn = e.target.closest('[data-action="toggle-site-detail"]');
  if (detailBtn) {
    const el = document.getElementById('sitedetail-' + detailBtn.dataset.siteUid);
    if (el) el.classList.toggle('visible');
    return;
  }

  // Toggle site note
  const siteNoteBtn = e.target.closest('[data-action="toggle-site-note"]');
  if (siteNoteBtn) {
    const area = siteNoteBtn.dataset.area;
    const site = siteNoteBtn.dataset.site;
    const noteId = 'sitenote-' + (area + '__' + site).replace(/[^a-zA-Z0-9_]/g, '_');
    const el = document.getElementById(noteId);
    if (el) el.classList.toggle('visible');
    return;
  }

  // CSV export
  const exportBtn = e.target.closest('[data-action="export-csv"]');
  if (exportBtn) {
    exportCSV();
    return;
  }

  // Save site note
  const saveSiteNote = e.target.closest('[data-action="save-site-note"]');
  if (saveSiteNote) {
    const el = document.getElementById(saveSiteNote.dataset.noteId);
    const notes = el.querySelector('textarea').value.trim();
    const fid = 'site__' + saveSiteNote.dataset.site.replace(/[^a-zA-Z0-9]/g, '_');
    setOutcome(saveSiteNote.dataset.area, fid, getOutcome(saveSiteNote.dataset.area, fid).status || 'pending', notes);
    el.classList.remove('visible');
    render();
    return;
  }

  // Save note
  const saveBtn = e.target.closest('[data-action="save-note"]');
  if (saveBtn) {
    const el = document.getElementById('note-' + saveBtn.dataset.uid);
    const textarea = el.querySelector('textarea');
    const notes = textarea.value.trim();
    const o = getOutcome(saveBtn.dataset.area, saveBtn.dataset.fid);
    setOutcome(saveBtn.dataset.area, saveBtn.dataset.fid, o.status, notes);
    el.classList.remove('visible');
    render();
    return;
  }
});

document.addEventListener('change', function(e) {
  // Checkbox selection
  const cb = e.target.closest('.site-checkbox');
  if (cb) {
    var key = cb.dataset.siteKey;
    if (cb.checked) selectedSites.add(key);
    else selectedSites.delete(key);
    // Update map and counter without full re-render (keeps scroll position)
    var mapOrder = selectedSites.size > 0 ? currentSiteOrder.filter(k => selectedSites.has(k)) : currentSiteOrder;
    updateMap(currentSiteGroups, mapOrder);
    document.getElementById('selCount').textContent = selectedSites.size > 0 ? '(' + selectedSites.size + ' selected)' : '';
    // Toggle card highlight
    var card = cb.closest('.fault-card');
    if (card) card.classList.toggle('selected', cb.checked);
    return;
  }

  // Site-level status dropdown — sets ALL faults at the site
  const sel = e.target.closest('[data-action="site-status"]');
  if (sel) {
    const fids = sel.dataset.fids.split(',');
    const area = sel.dataset.area;
    fids.forEach(function(fid) {
      const o = getOutcome(area, fid);
      setOutcome(area, fid, sel.value, o.notes);
    });
    render();
    return;
  }

  // Recategorise — updates the category in the FAULTS data
  const recat = e.target.closest('[data-action="recat"]');
  if (recat) {
    const fids = recat.dataset.fids.split(',');
    const area = recat.dataset.area;
    const newCat = recat.value;
    // Update the in-memory FAULTS array
    fids.forEach(function(fid) {
      for (var i = 0; i < FAULTS.length; i++) {
        if (FAULTS[i][0] === fid && FAULTS[i][2] === area) {
          FAULTS[i][5] = newCat;
        }
      }
    });
    // Save recategorisations to localStorage
    var recats = JSON.parse(localStorage.getItem('council_action_recats') || '{}');
    fids.forEach(function(fid) { recats[area + '|' + fid] = newCat; });
    localStorage.setItem('council_action_recats', JSON.stringify(recats));
    render();
    return;
  }
});

// Apply saved recategorisations on load
function applyRecats() {
  var recats = JSON.parse(localStorage.getItem('council_action_recats') || '{}');
  for (var key in recats) {
    var parts = key.split('|');
    var area = parts[0], fid = parts[1];
    for (var i = 0; i < FAULTS.length; i++) {
      if (FAULTS[i][0] === fid && FAULTS[i][2] === area) {
        FAULTS[i][5] = recats[key];
      }
    }
  }
}

// CSV export of currently filtered/visible data
function exportCSV() {
  var areaFilter = document.getElementById('areaFilter').value;
  var statusFilter = document.getElementById('statusFilter').value;
  var search = document.getElementById('searchInput').value.toLowerCase().trim();

  var rows = FAULTS.filter(function(f) {
    if (areaFilter && f[2] !== areaFilter) return false;
    var o = getOutcome(f[2], f[0]);
    if (statusFilter && o.status !== statusFilter) return false;
    if (activeCat && f[5] !== activeCat) return false;
    if (search) {
      var hay = (f[0] + ' ' + f[1] + ' ' + f[6] + ' ' + f[7] + ' ' + f[8]).toLowerCase();
      if (hay.indexOf(search) === -1) return false;
    }
    return true;
  });

  var csvLines = ['Fault ID,Site,Area,Created,IMTRAC Status,Category,Additional Info,Close Comments,Fault Code,Fault Description,Status,Notes'];
  rows.forEach(function(f) {
    var o = getOutcome(f[2], f[0]);
    var line = [f[0], f[1], f[2], f[3], f[4], f[5], f[6], f[7], f[8], f[9] || '', o.status, o.notes || ''];
    csvLines.push(line.map(function(v) {
      return '"' + String(v || '').replace(/"/g, '""') + '"';
    }).join(','));
  });

  var blob = new Blob(['\uFEFF' + csvLines.join('\n')], {type: 'text/csv;charset=utf-8'});
  var url = URL.createObjectURL(blob);
  var a = document.createElement('a');
  a.href = url;
  a.download = 'council-action-' + (areaFilter || 'all') + '-' + new Date().toISOString().slice(0,10) + '.csv';
  a.click();
  URL.revokeObjectURL(url);
}

// Fault age helper
function faultAge(dateStr) {
  if (!dateStr) return {text: '', cls: ''};
  var parts = dateStr.split('/');
  if (parts.length < 3) return {text: '', cls: ''};
  var d = new Date(parts[2].split(' ')[0], parseInt(parts[1])-1, parseInt(parts[0]));
  var now = new Date();
  var days = Math.floor((now - d) / 86400000);
  if (days < 0) return {text: '', cls: ''};
  if (days < 7) return {text: days + 'd ago', cls: 'age-recent'};
  if (days < 30) return {text: Math.floor(days/7) + 'w ago', cls: 'age-weeks'};
  if (days < 365) return {text: Math.floor(days/30) + 'mo ago', cls: 'age-months'};
  return {text: Math.floor(days/365) + 'y ago', cls: 'age-months'};
}

// Map
var faultMap = L.map('faultMap').setView([54.97, -1.61], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; OpenStreetMap',
  maxZoom: 18
}).addTo(faultMap);
var mapMarkers = L.layerGroup().addTo(faultMap);

function updateMap(siteGroups, siteOrder) {
  mapMarkers.clearLayers();
  var bounds = [];
  for (var i = 0; i < siteOrder.length; i++) {
    var faults = siteGroups[siteOrder[i]];
    var first = faults[0];
    var lat = parseFloat(first[10]), lon = parseFloat(first[11]);
    if (!lat || !lon) continue;
    var colour = CAT_COLOURS[first[5]] || '#6B7280';
    var marker = L.circleMarker([lat, lon], {
      radius: 8, fillColor: colour, color: '#fff',
      weight: 2, opacity: 1, fillOpacity: 0.85
    });
    marker.bindPopup('<b>' + esc(first[1]) + '</b><br>' + esc(first[5]) + '<br>' + faults.length + ' fault(s)<br><a href="https://www.google.com/maps/dir/?api=1&destination='+lat+','+lon+'" target="_blank">Navigate</a>');
    marker.addTo(mapMarkers);
    bounds.push([lat, lon]);
  }
  if (bounds.length > 0) faultMap.fitBounds(bounds, {padding: [30, 30]});
}

// Route planner — uses selected sites if any, otherwise pending sites in current filter
function planRoute(siteGroups, siteOrder) {
  var routeOrder = selectedSites.size > 0 ? siteOrder.filter(k => selectedSites.has(k)) : siteOrder;
  var waypoints = [];
  for (var i = 0; i < routeOrder.length; i++) {
    var faults = siteGroups[routeOrder[i]];
    var first = faults[0];
    var lat = parseFloat(first[10]), lon = parseFloat(first[11]);
    if (!lat || !lon) continue;
    if (selectedSites.size === 0) {
      var o = getOutcome(first[2], first[0]);
      if (o.status === 'completed') continue;
    }
    waypoints.push(lat + ',' + lon);
    if (waypoints.length >= 10) break;
  }
  if (waypoints.length === 0) { alert('No sites with coordinates to route to. Select some sites first.'); return; }
  var dest = waypoints.pop();
  var url = 'https://www.google.com/maps/dir/?api=1&destination=' + dest;
  if (waypoints.length > 0) url += '&waypoints=' + waypoints.join('|');
  window.open(url, '_blank');
}

var currentSiteGroups = {};
var currentSiteOrder = [];
var selectedSites = new Set();

// Init
loadLocalState();
applyRecats();
loadFromServer().then(() => render());

document.getElementById('areaFilter').addEventListener('change', render);
document.getElementById('statusFilter').addEventListener('change', render);
document.getElementById('searchInput').addEventListener('input', render);
</script>
</body>
</html>
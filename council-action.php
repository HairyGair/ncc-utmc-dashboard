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
    font-size: 15px; color: var(--text-mid); line-height: 1.5;
    margin-bottom: 10px;
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
  
  @media (max-width: 768px) {
    .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .controls { flex-direction: column; }
    .fault-footer { flex-direction: column; align-items: stretch; }
    .fault-footer select, .btn-sm { width: 100%; text-align: center; }
    .cat-progress { display: none; }
    .cat-progress-text { display: none; }
  }
</style>
</head>
<body>

<div class="topbar">
  <div>
    <h1>Council Action Tracker</h1>
    <div class="subtitle">NCC UTMC - Fault actions grouped by category</div>
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
  <div class="stats" id="statsBar"></div>
</div>

<div class="container">
  <div class="stats-row" id="statsRow"></div>
  <div id="content"></div>
</div>

<script>
const API_URL = 'api/index.php?page=council_action';

// Fault data: [faultId, site, area, created, imtracStatus, category, info, closeComments, faultCode]
const FAULTS = [
['F-006519','4022 - Leam Lane / Lingley Lane - Gateshead','Gateshead','21/03/2026 09:55','TemporaryClear','Investigate All Out','1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments'],
['F-006517','4002 - Oakwellgate / Nelson Street - Gateshead','Gateshead','19/03/2026 16:25','TemporaryClear','Post-RTC Repair','Stubb pole on deck.','','TS59 - Signal Heads RTA/Damaged;','See comments'],
['F-006516','5019 - Lambton Street / Tesco - Gateshead','Gateshead','19/03/2026 15:01','TemporaryClear','Post-RTC Repair','stub pole lying flat on ground','','TS62 - Signal Poles RTA/Damaged;','See comments'],
['F-006503','4130 - Northside Merge - Gateshead','Gateshead','11/03/2026 07:43','TemporaryClear','Comms / Router Reboot','Router online, however, no connection to controller. Can you check ethernet cable is connected to router/controller please.','','TS72 - No Comms with OTU;','See comments'],
['F-006502','4092 - Metro Perimeter / Cross Lane - Gateshead','Gateshead','10/03/2026 13:28','TemporaryClear','Cable / Electrical','power supply pillar corroded - has a hole in the bottom- noted whilst carrying a scheduled all other faults','','TS80 - Other Known Faults;','See comments'],
['F-006496','4130 - Northside Merge - Gateshead','Gateshead','09/03/2026 09:01','TemporaryClear','Comms / Router Reboot','UTC showing site offline - please reboot router.','','TS70 - OTU General Fault;','See comments'],
['F-006495','4050 - High Street / Charles Street - Gateshead','Gateshead','09/03/2026 08:52','TemporaryClear','Comms / Router Reboot','UTC showing site offline - please reboot router.','','TS70 - OTU General Fault;','See comments'],
['F-006492','4086 - High West Street / Arthur Street - Gateshead','Gateshead','06/03/2026 22:45','TemporaryClear','Post-RTC Repair','Traffic light on junction of High West Street and Arthur Street has been hit by a car and knocked to ground. It is right beside Gateshead police station, High West Street, NE8 1BN 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-006490','4093 - Felling Bypass - Abbotsford Road - Gateshead','Gateshead','06/03/2026 15:24','TemporaryClear','Post-RTC Repair','rtc  found by telent engineer','','TS80 - Other Known Faults;','See comments'],
['F-006436','5625 - Stargate Lane / Beweshill Lane - Gateshead','Gateshead','19/02/2026 10:28','TemporaryClear','Lamp / LED Replacement','lamp out','','TS80 - Other Known Faults;','See comments'],
['F-006400','5004 - Front Street / Rectory Lane - Gateshead','Gateshead','09/02/2026 07:46','TemporaryClear','MOVA / SCOOT','MOVA not operating, stuck on VA','','TS91 - MOVA Fault;','See comments'],
['F-006389','4130 - Northside Merge - Gateshead','Gateshead','06/02/2026 11:25','TemporaryClear','Check / Adjust Timings','Not letting enough of the traffic from the A1 through making the tailback all the way back to the angel on the A1  - will cause a massive accident as this is everyday since opening up the road from road works. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','TS80 - Other Known Faults;','See comments'],
['F-006386','5019 - Lambton Street / Tesco - Gateshead','Gateshead','06/02/2026 08:49','TemporaryClear','Cable / Electrical','Entrance/Exit to Tesco, Lambton Street, Gateshead. What looks like a traffic signals pillar has wires showing','','TSI - Site Inspection;','See comments'],
['F-006360','4141 - Felling Metro Station / Sunderland Road - Gateshead','Gateshead','02/02/2026 07:10','TemporaryClear','Comms / Router Reboot','5G Router offline, please power cycle off and on again to reboot','','TS72 - No Comms with OTU;','See comments'],
['F-006350','5016 - Thornley Drive / Rowlands Gill - Gateshead','Gateshead','29/01/2026 13:25','TemporaryClear','Realign Signal Head','Other The traffic light when you come down thornley road has been turned to look down lockhaugh road towards Winlaton mill.  Therefore dangerous as someone not knowing the area 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-006345','4004 - Felling Bypass / Green Lane - Gateshead','Gateshead','29/01/2026 06:25','TemporaryClear','Other / Needs Review','Other Lights are turning to red when approaching on the felling bypass . There is no traffic coming from green lane or the old fold estate. They used to turn red only when there was traffic coming f','','','See comments'],
['F-006323','4002 - Oakwellgate / Nelson Street - Gateshead','Gateshead','26/01/2026 08:55','TemporaryClear','Post-RTC Repair','Signal damaged Signal damaged following RTA 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-006322','4067 - Bensham Road / Cuthbert Street - Gateshead','Gateshead','24/01/2026 15:25','TemporaryClear','Other / Needs Review','Other There is no way for pedestrians to press a button to cross the road from one side. There is a button on the opposite side if you are walking down the hill passing the hotel but if you are tryi','','TS80 - Other Known Faults;','See comments'],
['F-006260','4093 - Felling Bypass - Abbotsford Road - Gateshead','Gateshead','21/01/2026 22:25','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) The traffic lights heading towards Heworth are out of sync.  They are mostly on red and the side road with limited traffic','-','-','See comments'],
['F-006252','5007 - A692 / Potters Wheel - Gateshead','Gateshead','20/01/2026 11:55','TemporaryClear','Loop / Slot Cutting','[Please check notes for rest of description]Other When travelling north west on the A6076, the lights used to change on approach if the was no traffic on Gatehead Road. They then changed so you have','-','TS80 - Other Known Faults;','See comments'],
['F-006236','5630 - A694 / Noel Avenue - Gateshead','Gateshead','15/01/2026 10:35','TemporaryClear','Post-RTC Repair','Signal damaged Vehicle has collided with traffic lights causing damage. The lights are wonky and will need to be assessed. Northumbria Police aware, incident NP-20260115-0269 refers','-','-','See comments'],
['F-006179','4022 - Leam Lane / Lingley Lane - Gateshead','Gateshead','22/12/2025 10:05','TemporaryClear','Post-RTC Repair','Signal damaged Pole damaged and lying on its side across pedestrian crossing with wires exposed 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATIO','-','-','See comments'],
['F-006167','5004 - Front Street / Rectory Lane - Gateshead','Gateshead','16/12/2025 15:35','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) it has been reported that the signals do not appear to be operating correctly and that only 4 vehicles are able to manoeuvr','-','-','See comments'],
['F-006158','4048 - Wellington Street / Link Road - Gateshead','Gateshead','10/12/2025 07:05','TemporaryClear','Realign Signal Head','Other Cycle signal head for West St NB requires re-aligning 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','-','TS80 - Other Known Faults;','See comments'],
['F-006138','4031 - Durham Road / Komatsu Access - Gateshead','Gateshead','03/12/2025 14:55','TemporaryClear','Other / Needs Review','Other If you are turning right onto harras bank from durham road the lights dont not allowed enough time, the priority is for the wrong side. Plus there are no lights on the other side of the juncti','-','TS80 - Other Known Faults;','See comments'],
['F-006132','4073 - Prince Consort Road / Shipcote Lane - Gateshead','Gateshead','01/12/2025 13:45','TemporaryClear','Check / Adjust Timings','Other issue with the timings 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','-','TS80 - Other Known Faults;','See comments'],
['F-006549','4033 - Eighton Lodge Roundabout - Gateshead','Gateshead','02/04/2026 09:55','TemporaryClear','Other / Needs Review','All lights across three lanes covered up on slip road and roundabout towards A1 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-006541','4130 - Northside Merge - Gateshead','Gateshead','30/03/2026 19:05','TemporaryClear','Lamp / LED Replacement','One traffic light damaged, another pointing the wrong way, lots of lamps out 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','','See comments'],
['F-003421','0403 - Queen Victoria Road / Great North Children\'s Hospital','Newcastle','23/03/2026 07:21','TemporaryClear','Post-RTC Repair','Veh Phase D all RAG aspects and Veh Phase F Green. Plus+ Nodes faulty Pole 4 Phase D (4LLCSD) and Pole 11 Phase F (11LLCSF).','','TSA - Vehicle Amber Lamp Out;TSG - Vehicle Green Lamp Out;TSR - Vehicle Red Lamp Out;','See comments'],
['F-003405','0019 - Heaton Road / Stephenson Road','Newcastle','16/03/2026 22:55','TemporaryClear','Check / Adjust Timings','The traffic lights do not stay green long enough for vehicles approaching the junction from Newton Road, which causes long queues to build up. Conversely, the lights often remain green longer than necessary for traffic coming from Heaton Road, even when there are no vehicles approaching the junction. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-003404','0352 - Scotswood Road / Refuse Access','Newcastle','16/03/2026 14:55','TemporaryClear','Loop / Slot Cutting','Reported this before leaving but nothing done with it, no high speed detection fitted, pedestrian crossings area across a high speed road with no safe method of control currently in operation meaning it is unsafe and does not meet DfT standards. It-s the same at the Cow Hill junction 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-003357','0240 - Chillingham Road / Warton Terrace','Newcastle','08/03/2026 17:35','TemporaryClear','Other / Needs Review','Pedestrian call buttons do not light up when pressed. Both units on either side of road. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','TS80 - Other Known Faults;','See comments'],
['F-003347','0301 - Denton Road / Whitfield Road','Newcastle','05/03/2026 18:25','TemporaryClear','Investigate All Out','Crossing fully out. 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments'],
['F-003314','0049 - West Road / Condercum Road','Newcastle','03/03/2026 15:45','TemporaryClear','Push Button / Pedestrian','Sound functionality not working for the lights on the junction of Condercum Road and West Road 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-003302','0042 - Denton Hotel / Silver Lonnen','Newcastle','28/02/2026 16:05','TemporaryClear','Other / Needs Review','1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-003288','0264 - Freeman Road / Hospital','Newcastle','26/02/2026 19:35','TemporaryClear','Investigate All Out','Traffic lights out altogether at busy crossing outside Freeman hospital next to bus stop on the side of the park 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','TSI - Site Inspection;','See comments'],
['F-003284','0236 - Welback Road / Monkchester Road','Newcastle','26/02/2026 09:52','TemporaryClear','Cable / Electrical','reports of a controller has been pushed over and wires exposed','','TS20 - Signals All Out;','See comments'],
['F-003282','0273 - Chillingham Road / Hartford Street','Newcastle','24/02/2026 21:15','TemporaryClear','Realign Signal Head','The first set of lights on the southbound side is turned round 90 degrees 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-003239','0129 - Scotswood Road / Business Park','Newcastle','20/02/2026 11:15','TemporaryClear','Post-RTC Repair','could this site be checked following 2 RTA this week causing near misses','','TSI - Site Inspection;','See comments'],
['F-003233','0271 - Stamfordham Road / Westward Court','Newcastle','20/02/2026 08:34','TemporaryClear','Lamp / LED Replacement','PED LIGHT OUT AND SIGNALS HEAD OUT','','TSWL - Pedestrian Wait Lamp Out;','See comments'],
['F-003229','0012 - Pilgrim Street / Blackett Street','Newcastle','18/02/2026 09:25','TemporaryClear','Push Button / Pedestrian','Green man button stuck on and also more time is now given to the pilgrim street side now for some reason. Also my previous report reference number 67906634 has still not been  fixed from barrack road bottom of stanhope street from 10th feb 1 Item Traffic Signals \ NON-URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - OUT OF SEQUENCE','','TS80 - Other Known Faults;','See comments'],
['F-003216','0059 - Westgate Road / WCR','Newcastle','15/02/2026 08:25','TemporaryClear','Investigate All Out','All lights out 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments'],
['F-003214','0286 - Rye Hill / Houston Street','Newcastle','14/02/2026 04:25','TemporaryClear','Post-RTC Repair','Temporarily traffic lights at the bottom of rye hill roundabout causing issues on houston street and many near miss collisions people trying to exit from Houston street where the primary school is. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-003208','0308 - Durant Road / College Street','Newcastle','13/02/2026 11:07','TemporaryClear','Post-RTC Repair','rtc','','TS59 - Signal Heads RTA/Damaged;','See comments'],
['F-003201','0401 - Barras Bridge / Kings Walk','Newcastle','12/02/2026 08:05','TemporaryClear','Comms / Router Reboot','Proroute router is not responding. Please power cycle off and on again to reboot.','','TS72 - No Comms with OTU;','See comments'],
['F-003193','0388 - Neville Street / Bewick Street','Newcastle','10/02/2026 16:15','TemporaryClear','Post-RTC Repair','post leaning 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-003191','0042 - Denton Hotel / Silver Lonnen','Newcastle','09/02/2026 16:05','TemporaryClear','Push Button / Pedestrian','Pedestrian element of this traffic signal has been smashed no green man for a number of months and needs replacing. Hard to establish if its safe to cross especially when busy traffic. 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-003184','0227 - Stanhope Street / Beaconsfield Street','Newcastle','08/02/2026 18:15','TemporaryClear','Lamp / LED Replacement','Other lights seem to be misaligned 1 Item Traffic Signals \ URGENT RESPONSE - SINGLE LIGHT OUT - RED','','TS80 - Other Known Faults;','See comments'],
['F-003177','0135A - Walker Road / Pottery Bank','Newcastle','07/02/2026 07:46','TemporaryClear','Other / Needs Review','this has been out since yesterday','','TS20 - Signals All Out;','See comments'],
['F-003175','0172 - West Central Route / Gallowgate','Newcastle','06/02/2026 11:25','TemporaryClear','Check / Adjust Timings','No sink with this light and the one further ahead on barrack road. Customer mentions long wait to proceed on barrack coming form Strawberry ln 1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','','See comments'],
['F-003173','0135A - Walker Road / Pottery Bank','Newcastle','06/02/2026 09:46','TemporaryClear','Investigate All Out','all out','','TS20 - Signals All Out;','See comments'],
['F-003171','1446 - West Denton Way / Downham','Newcastle','06/02/2026 07:59','TemporaryClear','Check / Adjust Timings','various reports signal not operating properly - failing to change  sequence issues','','TS80 - Other Known Faults;','See comments'],
['F-003162','0094 - Walker Road / Raby Street','Newcastle','04/02/2026 14:45','TemporaryClear','Check / Adjust Timings','Other Default setting wrong. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-003135','0050 - West Road / Copras Lane','Newcastle','02/02/2026 07:08','TemporaryClear','Comms / Router Reboot','5G Router offline, please power cycle off and on again to reboot','','TS72 - No Comms with OTU;','See comments'],
['F-003131','1406 - Stamfordham Road / Hillhead Road','Newcastle','01/02/2026 19:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Detection fault or damage as Stamfordham Road green extends when no vehicles are present, adding unnecessary delay for the Hillhead Road approach 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-003121','0129 - Scotswood Road / Business Park','Newcastle','29/01/2026 18:25','TemporaryClear','Lamp / LED Replacement','Multiple lights out Both green lights out for traffic heading East 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-003096','0275 - WCR / North West Radial','Newcastle','27/01/2026 00:05','TemporaryClear','Other / Needs Review','Signal damaged The light looks like a bus or lorry has hit it, the lights are hanging down. Graham from premier traffic management has reported in by telephone. He was carrying out a site check on A','','TS80 - Other Known Faults;','See comments'],
['F-002969','0271 - Stamfordham Road / Westward Court','Newcastle','23/01/2026 11:05','TemporaryClear','Lamp / LED Replacement','Single light out 298 stamfordham road 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-002922','0038 - WCR / Stanhope Street','Newcastle','12/01/2026 17:15','TemporaryClear','Lamp / LED Replacement','Multiple lights out Traffic lights damaged as a result of a car hitting them 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-002902','0049 - West Road / Condercum Road','Newcastle','05/01/2026 11:55','TemporaryClear','Other / Needs Review','Other Good morning, I realise that the regional signals team doesn\'t deal with road markings at signal-controlled junctions but I couldn\'t find another way to report this issue. At the northern arm,','','','See comments'],
['F-002895','0208 - Fenham Hall Drive / Wingrove Road','Newcastle','02/01/2026 08:35','TemporaryClear','Post-RTC Repair','Signal damaged Pedestrian crossing request column taken out by vehicle impact. Bits of tarmac and vehicle on the crossing. Location where student was run over in 2018 so some community impact. Junct','','TS80 - Other Known Faults;','See comments'],
['F-002893','0135A - Walker Road / Pottery Bank','Newcastle','31/12/2025 11:25','TemporaryClear','Investigate All Out','All lights out Not working. Crossing isn\'t working 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-002890','0038 - WCR / Stanhope Street','Newcastle','29/12/2025 23:35','TemporaryClear','Post-RTC Repair','Signal damaged Road traffic collision has damaged the lights at the junction of Barrick Road Blue Star Pub Police Control Room Incident log 10052025 Collar Number 4224','','','See comments'],
['F-002887','0147 - Gosforth High Street / Little Bridge','Newcastle','29/12/2025 06:35','TemporaryClear','Push Button / Pedestrian','Signal not operating correctly (e.g. failing to change or out of sequence) The green man is not displayed for enough time for pedestrians to cross the road. The traffic lights often change before pe','','TS80 - Other Known Faults;','See comments'],
['F-002859','0374 - Redheugh Bridge / Bridgehead','Newcastle','22/12/2025 12:35','TemporaryClear','Lamp / LED Replacement','Single light out Coming off Redheugh, northbound, upper high level set of lights out on the right. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGAT','','TS80 - Other Known Faults;','See comments'],
['F-002857','0059 - Westgate Road / WCR','Newcastle','22/12/2025 12:35','TemporaryClear','Other / Needs Review','Signal damaged St James Boulevard northbound - right hand signal damaged and not working for straight ahead lanes 1 Item Traffic Signals \ TRAFFIC SIGNALS','','','See comments'],
['F-002855','0129 - Scotswood Road / Business Park','Newcastle','21/12/2025 08:55','TemporaryClear','Investigate All Out','Multiple lights out all lights out at the junctions william armstrong road scotswood road officers on scene reported by police officer simon hayes 2566 1','','TS80 - Other Known Faults;','See comments'],
['F-002814','0003 - Westgate Road / Clayton Street','Newcastle','08/12/2025 10:15','TemporaryClear','Comms / Router Reboot','Other UTC showing router is offline, needs rebooting or plugging in. Please ring UTMC office when on site. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT','','','See comments'],
['F-002799','0007 - Grainger Street / Newgate Street','Newcastle','06/12/2025 10:35','TemporaryClear','Other / Needs Review','Signal damaged Pedestrian countdown light damaged and not working. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-002795','0130 - Scotswood Road / Vickers','Newcastle','05/12/2025 08:55','TemporaryClear','Other / Needs Review','Other outside of pearsons engineering there is a traffic light on pedestrian crossing with exposed wires hanging down onto the crossing reported by police 4033','','TS80 - Other Known Faults;','See comments'],
['F-002780','0040 - Denton Road / Whickham View','Newcastle','02/12/2025 21:25','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) The pedestrian crossing is not working correctly. The signal comes on automatically on repeat without anyone pressing the b','','','See comments'],
['F-002769','0309 - Kenton Lane / Drayton Road','Newcastle','30/11/2025 19:05','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) The signals used to only be triggered to change if someone was waiting to come out of Drayton Road however since roadworks','','TS95 - Slot Cutting Required;','See comments'],
['F-002745','0356 - Kenton Lane / Kenton School','Newcastle','26/11/2025 11:15','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Pedestrian button stuck in constantly being pressed. Traffic lights also not operating correctly after road resurfacing. Ta','','TS61 - Detectors (Loops) RTA/Damaged;','See comments'],
['F-002742','0314 - Brunton Lane / Tudor Way','Newcastle','25/11/2025 20:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Ever since the road was resurfaced the traffic lights stay red for upwards of 2 minutes for Tudor Way and does not stay gre','','TS95 - Slot Cutting Required;','See comments'],
['F-002732','0059 - Westgate Road / WCR','Newcastle','24/11/2025 15:28','TemporaryClear','Post-RTC Repair','reported pole leading into the road police on site','','TS67 - RTA;','See comments'],
['F-002729','0407 - Newgate Street / St. Andrew\'s Street','Newcastle','24/11/2025 09:25','TemporaryClear','Comms / Router Reboot','Other UTC showing router is offline, needs rebooting. Please ring UTMC office when on site. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-002704','0019 - Heaton Road / Stephenson Road','Newcastle','19/11/2025 17:55','TemporaryClear','Loop / Slot Cutting','[Please check notes for rest of description]Other In the evening lights only allow two cars throughbefore changing yet lights for cars from Heaton Road allows much longer green. Results in unfair wa','','TS95 - Slot Cutting Required;','See comments'],
['F-002703','0147 - Gosforth High Street / Little Bridge','Newcastle','19/11/2025 11:11','TemporaryClear','Other / Needs Review','request a review of the traffic lights. For pedestrians, the lights change from green to red too quickly for even non-disabled adults to cross, and for children, the elderly, disabled or pregnant people, there is not enough time to safely cross the full road before cars start moving again. note the lights seem to be on a longer setting during non-peak times, if this setting could be maintained throughout the day it would make the road significantly safer for pedestrians.','','TS39 - Timing Errors;','See comments'],
['F-002686','0275 - WCR / North West Radial','Newcastle','18/11/2025 13:14','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TSA - Vehicle Amber Lamp Out;','See comments'],
['F-002685','0248 - City Road / Milk Market','Newcastle','18/11/2025 13:14','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TS62 - Signal Poles RTA/Damaged;','See comments'],
['F-002680','0037 - Sandyford Road / Portland Terrace','Newcastle','18/11/2025 12:59','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TS10 - Single Lamp Out;','See comments'],
['F-002675','0080 - John Dobson Street / St. Mary\'s Place','Newcastle','18/11/2025 09:12','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TS10 - Single Lamp Out;','See comments'],
['F-002673','0056 - Neville Street / Clayton Street','Newcastle','18/11/2025 09:11','TemporaryClear','Lamp / LED Replacement','Identified lamp fault','','TSR - Vehicle Red Lamp Out;','See comments'],
['F-003440','0014 - Pilgrim Street / Market Street','Newcastle','29/03/2026 16:25','TemporaryClear','Check / Adjust Timings','The cycle lights on the north end of the Pilgrim Street cycle lane don\'t ever change to green and the beg button appears to be broken. I waited almost 4 minutes and three cycles of the main lights and there was no change, making it very difficult to cross the junction. 1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','','See comments'],
['F-003439','1025 - Great Park Spine Road / Pegasus','Newcastle','28/03/2026 19:35','TemporaryClear','Cable / Electrical','traffic light signal damaged, wires exposed 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-003438','0262 - Armstrong Road / Clara Street','Newcastle','28/03/2026 13:25','TemporaryClear','Other / Needs Review','1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-001874','3309 - Holystone Bypass / Dual Toucan','North Tyneside','22/03/2026 05:55','TemporaryClear','Other / Needs Review','Traffic light hit be car torn off exposed wires 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-001867','2842 - Silverlink / Mallard Way','North Tyneside','16/03/2026 08:43','TemporaryClear','Check / Adjust Timings','Extreme delays at silverpoint car park near next/wren/hobbycraft. Traffic lights only allow 2/3 cars out at a time causing congestion and delays up to 45 mins to exit the car park. This has been a an ongoing problem for several months now without being resolved.','','TSS - General Survey/Timings;','See comments'],
['F-001859','2062 - Shields Road / Foxhunters Road','North Tyneside','12/03/2026 23:45','TemporaryClear','Post-RTC Repair','Outside Aldi. A car has hit the light pole. Glass and plastic on pavement and road. 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-001832','3296 - Earsdon Road / Red Lion Pub','North Tyneside','03/03/2026 08:55','TemporaryClear','Push Button / Pedestrian','The pedestrian crossing isn-t working on either side. School kids use this to get across the busy road. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','TSI - Site Inspection;','See comments'],
['F-001794','3662 - Westmoor Roundabout','North Tyneside','19/02/2026 13:22','TemporaryClear','Other / Needs Review','found while attending 3661','','TS59 - Signal Heads RTA/Damaged;','See comments'],
['F-001748','3263 - Great Lime Road / Killingworth Road','North Tyneside','10/02/2026 16:15','TemporaryClear','Post-RTC Repair','post leaning 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-001679','2060 - Coast Road / Billy Mill','North Tyneside','27/01/2026 13:45','TemporaryClear','Realign Signal Head','Signal damaged Green light (nearest to Tesco-s) broken and facing wrong direction. Traffic light still operational. 1 Item Traffic Signals \ TRAFFIC SIGN','','TS80 - Other Known Faults;','See comments'],
['F-001643','3300 - Great Lime Road / Palmersville','North Tyneside','23/01/2026 21:55','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Faulty traffic signals. Stopping traffic for no reason when there is no traffic to stop for.','','TS80 - Other Known Faults;','See comments'],
['F-001585','2045 - Shiremoor Bypass / Grey Horse Pegasus','North Tyneside','02/01/2026 07:55','TemporaryClear','Post-RTC Repair','Other Pole 9 There is a fault on the ANPR cable somewhere between the controller and the top of the pole causing the voltage to be pulled down from 48VDC to 25VDC with load on and 35VDC with no load','','TS80 - Other Known Faults;','See comments'],
['F-001584','3635 - A189 / Weetslade Roundabout','North Tyneside','01/01/2026 02:35','TemporaryClear','Post-RTC Repair','Other City security reported there has been a Road accident a street lamp has been hit and now traffic lights on the roundabout are completely off. 1 Item T','','TS80 - Other Known Faults;','See comments'],
['F-001580','3260 - Salters Lane / West Farm Avenue','North Tyneside','30/12/2025 01:05','TemporaryClear','Post-RTC Repair','Signal damaged post leaning - may be a danger to pedestrians 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-001553','3260 - Salters Lane / West Farm Avenue','North Tyneside','21/12/2025 00:45','TemporaryClear','Post-RTC Repair','Signal damaged Northumbria police log NP-20251220-1258, relates to traffic lights at the junction of Salters lane and West Farm Avenue has been taken out, exposed wires','','','See comments'],
['F-001539','3244 - Great Lime Road / Southgate','North Tyneside','15/12/2025 16:15','TemporaryClear','Post-RTC Repair','Signal damaged A tractor has knocked the lights over and wires exposed 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-001534','3300 - Great Lime Road / Palmersville','North Tyneside','15/12/2025 09:06','TemporaryClear','Check / Adjust Timings','Traffic light at junction of Great lime Road and Forest Gate appear to be causing traffic congestion. The flow of traffic is appalling. The lights only used to change when cars were exiting the estate as if on a sensor. They now change when there are no cars exiting. Not sure if building works/site is causing them to change more regularly than needed.','','TSS - General Survey/Timings;','See comments'],
['F-001533','2062 - Shields Road / Foxhunters Road','North Tyneside','15/12/2025 08:35','TemporaryClear','Other / Needs Review','Other Lights missing from one side of street 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-001495','2432 - Beach Road / Preston Road North','North Tyneside','05/12/2025 09:55','TemporaryClear','Push Button / Pedestrian','There is currently a fault with the staggered signalised crossing on Preston North Road, near its roundabout with Beach Road. We have had a report that the push button unit has fallen away from the post and is hanging by the wires.','','TS55 - Faulty Push Buttons;','See comments'],
['F-001492','3251-EAST - A19 / Holystone Roundabout East','North Tyneside','04/12/2025 12:05','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Waited on New York road, for around 8 minutes as lights o to roundabout did not change','','TS80 - Other Known Faults;','See comments'],
['F-001482','3268 - Whitley Road / Station Road','North Tyneside','27/11/2025 20:35','TemporaryClear','Loop / Slot Cutting','Other Activation button on pedestrian crossing is stuck as pushed in all the time, it triggers green light for pedestrians even nobody is there. I couldn\'t release it, it\'s stuck. It builds up traff','','TS95 - Slot Cutting Required;','See comments'],
['F-001457','3251-EAST - A19 / Holystone Roundabout East','North Tyneside','20/11/2025 12:25','TemporaryClear','Lamp / LED Replacement','Single light out Single red light out for holystone roundabout when going southbound 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','-','TS80 - Other Known Faults;','See comments'],
['F-001455','3659 - Lockey Park / Wideopen','North Tyneside','20/11/2025 06:45','TemporaryClear','Post-RTC Repair','Signal damaged Traffic signal on junction of Great North Road Havannah Drive, NE13 6LD has been knocked over by a car and is currently in the middle of the road Police rang this in. Log number LatLo','-','TS80 - Other Known Faults;','See comments'],
['F-001453','3268 - Whitley Road / Station Road','North Tyneside','19/11/2025 16:35','TemporaryClear','Check / Adjust Timings','[Please check notes for rest of description]Other The sequence appears to have been rephased so that, on occasion, traffic on Whitley Road going West to East contiunues with a green light whilst the','-','TS80 - Other Known Faults;','See comments'],
['F-001429','2045 - Shiremoor Bypass / Grey Horse Pegasus','North Tyneside','17/11/2025 07:45','TemporaryClear','Post-RTC Repair','Other Pole 9.  Fault on the cable for ANPR camera somewhere between the controller and the top of the pole causing the voltage to be pulled down from 48VDC to 25VDC with load on and 35VDC with no lo','-','TS80 - Other Known Faults;','See comments'],
['F-001920','2842 - Silverlink / Mallard Way','North Tyneside','08/04/2026 10:15','TemporaryClear','Post-RTC Repair','traffic lights been knocked down in a police pursuit on roundabout near silverlink sliproad leading onto A 19 nearest postcode NE29 7TE 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-001917','3268A - Station Road / Percy Hedley','North Tyneside','06/04/2026 03:05','TemporaryClear','Post-RTC Repair','traffic light is on the road 1 Item Traffic Signals \ URGENT RESPONSE - MULTIPLE LIGHTS OUT','','','See comments'],
['F-001916','2035 - New York Way / Middle Engine Lane','North Tyneside','05/04/2026 18:55','TemporaryClear','Investigate All Out','1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments'],
['F-001890','3319 - Killingworth Road / Hollywood Avenue','North Tyneside','28/03/2026 09:55','TemporaryClear','Post-RTC Repair','signal damaged after RTC - wires exposed on pedestrian button press - reported by pc Laurie parker 5193 - police ref 178 28022026 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-001889','3251-WEST - A19 / Holystone Roundabout West','North Tyneside','27/03/2026 16:15','TemporaryClear','Check / Adjust Timings','lights out of sequence, causing traffic to back up 1 Item Traffic Signals \ NON-URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - OUT OF SEQUENCE','','','See comments'],
['F-001884','3294 - Forest Hall Road / Delaval Road','North Tyneside','25/03/2026 09:25','TemporaryClear','Post-RTC Repair','The pedestrian crossing button unit is hanging off the pole on one side of the road, with wires exposed.  It has been like this for months, but had been taped on - now someone has ripped the tape off 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-001425','BV08 - Cowpen Road / Asda - Blyth Valley','Northumberland','23/02/2026 14:54','TemporaryClear','Post-RTC Repair','Exposed wires due to rotten pole, enough for kids to get their hands in','-','TS80 - Other Known Faults;','See comments'],
['F-001422','BV71 - Bridge Street / Quay Road','Northumberland','17/02/2026 10:48','TemporaryClear','Other / Needs Review','The signals are not working at all','-','TS20 - Signals All Out;','See comments'],
['F-001419','W46 - North Seaton Road / Newbiggin Road','Northumberland','16/02/2026 15:04','TemporaryClear','Other / Needs Review','site has been out since before xmas but nothing in the comments as to why','-','TS20 - Signals All Out;','See comments'],
['F-001393','BV23 - Low Main Place / Dudley Lane - Blyth Valley','Northumberland','06/02/2026 08:29','TemporaryClear','Investigate All Out','All pedestrian and vehicle lights out','-','TS20 - Signals All Out;','See comments'],
['F-001345','BV71 - Bridge Street / Quay Road','Northumberland','14/01/2026 16:46','TemporaryClear','Check / Adjust Timings','SIGNALS STUCK ON RED','-','TS30 - Sticking on Red;','See comments'],
['F-001327','BV47 - Front Street / Lamb Street - Blyth Valley','Northumberland','03/01/2026 00:15','TemporaryClear','Post-RTC Repair','Signal damaged Signal damaged in RTC. Exposed wires and signal laying on the ground. Police log 1008-02.01.26 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT','','TS80 - Other Known Faults;','See comments'],
['F-001320','A3 - Bondgate Without / Playhouse - Alnwick','Northumberland','30/12/2025 19:08','TemporaryClear','Investigate All Out','All out','','TS20 - Signals All Out;','See comments'],
['F-001316','BV18 - Cowpen Road / Tynedale Drive - Blyth Valley','Northumberland','26/12/2025 20:06','TemporaryClear','Post-RTC Repair','RTC - Traffic lights have been damaged  Wires exposed','','TS64 - Cables RTA/Damaged;','See comments'],
['F-001314','BV31A - A1171 Dudley Lane / Cramlington N/BD - Blyth Valley','Northumberland','23/12/2025 13:12','TemporaryClear','Loop / Slot Cutting','Delay in signal for pedestrians to cross','','TS52 - Not Demanding (or Extending);','See comments'],
['F-001294','BV02 - Bridge Street / Union Street - Blyth Valley','Northumberland','08/12/2025 10:17','TemporaryClear','Other / Needs Review','the site number isn\'t on imtrac the site is Quay Road/Bridge Street Blyth','','TS20 - Signals All Out;','See comments'],
['F-001477','BV20 - Main Street / Seghill - Blyth Valley','Northumberland','07/04/2026 07:52','TemporaryClear','Post-RTC Repair','Reported that head is twisted due to vehicle impact','','TS59 - Signal Heads RTA/Damaged;','See comments'],
['F-001470','BV10 - Rotary Way / Amersham Road - Blyth Valley','Northumberland','01/04/2026 09:06','TemporaryClear','Other / Needs Review','ref 9222646','','TS20 - Signals All Out;','See comments'],
['F-001223','6505 - Front Street / East Street','South Tyneside','14/03/2026 11:15','TemporaryClear','Check / Adjust Timings','1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','TS80 - Other Known Faults;','See comments'],
['F-001211','6060 - Sunderland Road / Grosvenor Road','South Tyneside','06/03/2026 15:55','TemporaryClear','Check / Adjust Timings','Lights only allowing a few cars through before cycling to next direction 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-001190','6855 - Addison Road / Hylton Lane','South Tyneside','03/03/2026 05:45','TemporaryClear','Loop / Slot Cutting','Once upon a time the signal on Hylton Lane was Smart i.e. when you approached it on Red it would instantly turn to Green if there was no-one else around. I used to notice this as I was going through very early in the morning.  Can\'t be a coincidence but just after resurfacing works were carried out where everything was switched off and then put back on they stopped being Smart and now you are kept waiting for ages. Can they be put back. 1 Item Traffic Signals \ NON-URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - OUT OF SEQUENCE','','TS80 - Other Known Faults;','See comments'],
['F-001121','7505 - Albert Road / Park Road','South Tyneside','03/02/2026 13:25','TemporaryClear','Lamp / LED Replacement','Multiple lights out traffic lights out 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-001109','6858 - A194 / Mill Lane','South Tyneside','30/01/2026 09:45','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Heading south the pegasus crossing constantly activate when nothing crossing. It seems to be faulty. 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-001105','6060 - Sunderland Road / Grosvenor Road','South Tyneside','29/01/2026 07:45','TemporaryClear','Investigate All Out','All lights out only green light working 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-001077','6060 - Sunderland Road / Grosvenor Road','South Tyneside','26/01/2026 08:35','TemporaryClear','Lamp / LED Replacement','Single light out light stuck on red 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','TS80 - Other Known Faults;','See comments'],
['F-000983','6058 - John Reid Road / Fire Station','South Tyneside','22/12/2025 09:55','TemporaryClear','Investigate All Out','All lights out 1 Item Traffic Signals \ TRAFFIC SIGNALS FAULT INVESTIGATION','','','See comments'],
['F-000976','6505 - Front Street / East Street','South Tyneside','18/12/2025 15:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) As you turn left at the lights to go toward seafront the lights are only letting 3,4 car\'s out and the traffic is building','','TS61 - Detectors (Loops) RTA/Damaged;','See comments'],
['F-000975','7517 - Victoria Road / Mill Lane','South Tyneside','18/12/2025 14:54','TemporaryClear','Check / Adjust Timings','this fault has been reported at  14:21 today after the initial was investigate at 08:45, not sure if timings have been changed and this is why this is causing so much congestion now. i have video footage of the timing issues here, who ever is responding to this fault let me know and i can share it with you.','','TS39 - Timing Errors;','See comments'],
['F-000973','7517 - Victoria Road / Mill Lane','South Tyneside','18/12/2025 08:45','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Lights only remaining on green for a very short period and then going back to red','','','See comments'],
['F-000972','7510 - Victoria Road / Station Road','South Tyneside','18/12/2025 08:05','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) customer reports that the lights are out of sync and are only letting 2 to 3 cars through at a time, so there are large que','','TS61 - Detectors (Loops) RTA/Damaged;','See comments'],
['F-000971','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 23:15','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Traffic travelling on Victoria Road west only have 4-5sec on green considerable tail backs.','','TS61 - Detectors (Loops) RTA/Damaged;','See comments'],
['F-000968','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 11:15','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) Traffic lights at the junction of Mill Lane and Victoria Road West are only letting 4 cars through there is a large tail ba','','TS61 - Detectors (Loops) RTA/Damaged;','See comments'],
['F-000966','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 09:15','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Lights have been put back on after work was completed but are out of sync and only letting a couple cars through causing ta','','','See comments'],
['F-000965','7517 - Victoria Road / Mill Lane','South Tyneside','17/12/2025 08:55','TemporaryClear','Check / Adjust Timings','Signal not operating correctly (e.g. failing to change or out of sequence) Thee is a job already reported on november 26 for this same Signal. The customer says that the light when going north east','','TS50 - Detection Faults;','See comments'],
['F-000962','6861(EAST) - A19/A1290 / East Side of Roundabout','South Tyneside','13/12/2025 02:35','TemporaryClear','Post-RTC Repair','Signal damaged Car hit traffic light in road traffic accident caller not certain if it is this selected traffic signal or the one next to it Site No 6861E A19 A1290 East South Tyneside Tyne and Wear','','','See comments'],
['F-000915','7517 - Victoria Road / Mill Lane','South Tyneside','26/11/2025 09:45','TemporaryClear','Loop / Slot Cutting','Signal not operating correctly (e.g. failing to change or out of sequence) the timing are out on these lights as there is only a few car\'s getting through 1','','TS61 - Detectors (Loops) RTA/Damaged;','See comments'],
['F-000870','6047 - John Reid Road / Perth Avenue','South Tyneside','18/11/2025 09:47','TemporaryClear','Lamp / LED Replacement','1st Level Red Lamp Fault','','TSR - Vehicle Red Lamp Out;','See comments'],
['F-001244','7205 - Newcastle Road / Jarrow Road','South Tyneside','27/03/2026 12:13','TemporaryClear','Comms / Router Reboot','5G Proroute router has stopped responding. Please try to power down the router and then power up to trigger a reboot.','','TS70 - OTU General Fault;','See comments'],
['F-006208','8106 - Wheatsheaf Gyratory - Sunderland','Sunderland','16/03/2026 21:25','TemporaryClear','Controller / Cabinet','The controller green box door is on the ground next to the controller box all wires are showing it is next to the pelican crossing on Newcastle Road further down from Tesco Extra. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-006192','9630E - A1231 Part Time Signals / A182  - Sunderland','Sunderland','09/03/2026 07:20','TemporaryClear','Comms / Router Reboot','UTC showing All Lamps Off','','TS20 - Signals All Out;','See comments'],
['F-006116','8026 - Harbour View / Roker Terrace','Sunderland','19/02/2026 09:57','TemporaryClear','Post-RTC Repair','Pole 9 has a lot of movement within the NAL socket and needs adjusting slighting this was very noticeable on 18/02/2026 due to the high winds.','','TSI - Site Inspection;','See comments'],
['F-006093','8606 - Silksworth Terrace / Blind Lane - Sunderland','Sunderland','11/02/2026 09:45','TemporaryClear','Check / Adjust Timings','not changing for the pedestrians but changes for cars please investigate thank you 1 Item Traffic Signals \ URGENT RESPONSE - SIGNAL NOT OPERATING CORRECTLY - FAILING TO CHANGE','','','See comments'],
['F-005940','8020 - Clockwell Street / Northern Way - Sunderland','Sunderland','19/01/2026 10:33','TemporaryClear','Comms / Router Reboot','router offline, please reboot.','','TS70 - OTU General Fault;','See comments'],
['F-005925','8006 - Newcastle Road / Thompson Road - Sunderland','Sunderland','06/01/2026 22:05','TemporaryClear','Post-RTC Repair','Signal damaged Police have called to report the signal damaged after a vehicle hit it wires are sparking Police log-NP 20260106 0968--Police collar 9138 Traffic light is on the A1018 flyover on Newc','','TS80 - Other Known Faults;','See comments'],
['F-005923','8152 - West Wear Street / William Street - Sunderland','Sunderland','06/01/2026 10:16','TemporaryClear','Lamp / LED Replacement','needs new head - seen by telent','','TS80 - Other Known Faults;','See comments'],
['F-005919','8184 - Hillthorn Park / Nissan Way - Sunderland','Sunderland','03/01/2026 14:05','TemporaryClear','Post-RTC Repair','Signal damaged traffic light infiniti drive junction with nissan way knocked over as a result of single vehicle rtc, traffic light completely felled onto the path','','TS80 - Other Known Faults;','See comments'],
['F-005918','8204 - Whitburn Road / Seaburn Park','Sunderland','01/01/2026 08:45','TemporaryClear','Post-RTC Repair','Signal damaged lights works but pole bent following an RTC - police log 1149 31122025 - rang through by Sunderland Council 1 Item Traffic Signals \ TRAFFI','','TS80 - Other Known Faults;','See comments'],
['F-005898','9615 - A1290 / Cherry Blossom Way','Sunderland','22/12/2025 06:55','TemporaryClear','Check / Adjust Timings','[Please check notes for rest of description]Signal not operating correctly (e.g. failing to change or out of sequence) I also noticed this issue on Friday. The lights on A1290 are only staying on gr','','','See comments'],
['F-005748','8024 - A19 / A1231 Part Time Signals - Sunderland','Sunderland','04/11/2025 19:58','TemporaryClear','Lamp / LED Replacement','Please select an option that best describes the problem: Single light out Green lights are not working on two sets of lights Green light not on Site 8024 A19  A1231 Sunderland Tyne  Wear as the location of the issue','','TS80 - Other Known Faults;','See comments'],
['F-006270','8076 - Washington Road / Ferryboat Lane','Sunderland','07/04/2026 22:55','TemporaryClear','Post-RTC Repair','Traffic light hit in road traffic accident wires exposed police log 1214.07042026 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-006265','8622 - Doxford Park Way E/B + W/B / West Bound - Sunderland','Sunderland','07/04/2026 09:25','TemporaryClear','Investigate All Out','Traffic lights are off again, when lights are fixed they only work for a couple of hours and then go off again. Lights need looked at properly as pedestrain crossing lights are not working correctly. Concerns over if lights don\'t get fixed, someone will get hurt when crossing the road due to amount of cars speeding when lights are out. 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','','See comments'],
['F-006257','8014 - North Bridge Street / Dame Dorothy Street - Sunderland','Sunderland','04/04/2026 09:26','TemporaryClear','Other / Needs Review','Damaged traffic light - slightly bend to the side, due to road traffic collusion','','TS62 - Signal Poles RTA/Damaged;','See comments'],
['F-006249','9289 - Pemberton Street / Front Street - Sunderland','Sunderland','01/04/2026 12:15','Open','Check / Adjust Timings','Residents have requested that a traffic light survey be carried out, as the traffic signals in Hetton are causing significant tailbacks during peak times. This is particularly noticeable at the pedestrian crossing outside Tesco, where the signals appear to change almost immediately after traffic begins to move. As a result, vehicles are frequently being stopped, which prevents a smooth flow of traffic and leads to congestion in the area. 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-006248','8622 - Doxford Park Way E/B + W/B / West Bound - Sunderland','Sunderland','01/04/2026 11:05','TemporaryClear','Investigate All Out','all lights off since last Thursday 26.03.26 1 Item Traffic Signals \ URGENT RESPONSE - ALL LIGHTS OUT','','TS20 - Signals All Out;','See comments'],
['F-006243','9637 - Vigo Lane / Picktree Lane','Sunderland','30/03/2026 07:45','TemporaryClear','Loop / Slot Cutting','Since new paths were put in, the Vigo lane lights were only letting 3 cars out, that has improved however when they change for other cars where you very rarely have more than 2 cars sometimes none it-s on that way for a lot longer 1 Item Traffic Signals \ NON-URGENT RESPONSE - OTHER','','','See comments'],
['F-006240','9630E - A1231 Part Time Signals / A182  - Sunderland','Sunderland','28/03/2026 00:55','TemporaryClear','Post-RTC Repair','URGENT, Police rang to report a vehicle has crashed into traffic lights on Sunderland roundabout A182   A1231, there are exposed wires showing. log number 1190 27th march 1 Item Traffic Signals \ URGENT RESPONSE - SIGNALS DAMAGED','','TS80 - Other Known Faults;','See comments'],
['F-006234','8024 - A19 / A1231 Part Time Signals - Sunderland','Sunderland','25/03/2026 10:05','TemporaryClear','Lamp / LED Replacement','Two of the three traffic lights are not visible as turned away . if the only one left fails traffic joining from the A19 will have to treat the junction as a normal roundabout with resulting danger. 1 Item Traffic Signals \ URGENT RESPONSE - MULTIPLE LIGHTS OUT','','','See comments'],
['F-006228','9292 - Easington Lane / Murton Lane - Sunderland','Sunderland','24/03/2026 15:16','TemporaryClear','Investigate All Out','All out','','TS80 - Other Known Faults;','See comments']
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

function render() {
  const areaFilter = document.getElementById('areaFilter').value;
  const statusFilter = document.getElementById('statusFilter').value;
  const search = document.getElementById('searchInput').value.toLowerCase().trim();
  
  // Filter faults
  const filtered = FAULTS.filter(f => {
    if (areaFilter && f[2] !== areaFilter) return false;
    const o = getOutcome(f[2], f[0]);
    if (statusFilter && o.status !== statusFilter) return false;
    if (search) {
      const hay = (f[0] + ' ' + f[1] + ' ' + f[6] + ' ' + f[7]).toLowerCase();
      if (!hay.includes(search)) return false;
    }
    return true;
  });
  
  // Group by category
  const groups = {};
  for (const cat of CATEGORIES) groups[cat] = [];
  for (const f of filtered) {
    const cat = f[5];
    if (groups[cat]) groups[cat].push(f);
    else groups[cat] = [f];
  }
  
  // Stats
  const total = filtered.length;
  const pending = filtered.filter(f => getOutcome(f[2], f[0]).status === 'pending').length;
  const inProgress = filtered.filter(f => getOutcome(f[2], f[0]).status === 'in_progress').length;
  const completed = filtered.filter(f => getOutcome(f[2], f[0]).status === 'completed').length;
  const escalated = filtered.filter(f => getOutcome(f[2], f[0]).status === 'escalated').length;

  document.getElementById('statsRow').innerHTML =
    '<div class="stat-card stat-total"><div class="stat-number">' + total + '</div><div class="stat-label">Total faults</div></div>' +
    '<div class="stat-card stat-pending"><div class="stat-number">' + pending + '</div><div class="stat-label">Pending</div></div>' +
    '<div class="stat-card stat-progress"><div class="stat-number">' + inProgress + '</div><div class="stat-label">In progress</div></div>' +
    '<div class="stat-card stat-completed"><div class="stat-number">' + completed + '</div><div class="stat-label">Completed</div></div>';
  
  // Render
  const container = document.getElementById('content');
  let html = '';
  
  for (const cat of CATEGORIES) {
    const items = groups[cat];
    if (!items || items.length === 0) continue;
    
    const colour = CAT_COLOURS[cat] || '#6B7280';
    const catCompleted = items.filter(f => getOutcome(f[2], f[0]).status === 'completed').length;
    const catPct = items.length > 0 ? Math.round((catCompleted / items.length) * 100) : 0;
    html += '<div class="cat-section" id="cat-' + cat.replace(/[^a-zA-Z]/g, '') + '">';
    html += '<div class="cat-header" data-toggle="cat">';
    html += '<span class="cat-badge" style="background:' + colour + '">' + items.length + '</span>';
    html += '<span class="cat-name">' + esc(cat) + '</span>';
    html += '<div class="cat-progress"><div class="cat-progress-bar" style="width:' + catPct + '%"></div></div>';
    html += '<span class="cat-progress-text">' + catPct + '%</span>';
    html += '<span class="cat-toggle">&#9660;</span>';
    html += '</div>';
    html += '<div class="cat-body">';
    
    for (const f of items) {
      const o = getOutcome(f[2], f[0]);
      const statusClass = o.status !== 'pending' ? ' status-' + o.status : '';
      const fid = f[0];
      const uid = f[2] + '__' + fid;
      
      html += '<div class="fault-card' + statusClass + '" data-uid="' + uid + '">';

      // Clean card: site name + status. That's it.
      html += '<div class="fault-top">';
      html += '<div class="fault-site">' + esc(f[1]) + '</div>';
      html += '<span class="status-pill ' + o.status + '">' + o.status.replace('_', ' ') + '</span>';
      html += '</div>';

      // Notes (full, prominent — only if set)
      if (o.notes) {
        html += '<div class="fault-notes-display"><strong>Your notes</strong>' + esc(o.notes) + '</div>';
      }

      // Actions row
      html += '<div class="fault-footer">';
      html += '<select data-area="'+esc(f[2])+'" data-fid="'+esc(fid)+'" data-action="status">';
      html += '<option value="pending"' + (o.status==='pending'?' selected':'') + '>Pending</option>';
      html += '<option value="in_progress"' + (o.status==='in_progress'?' selected':'') + '>In Progress</option>';
      html += '<option value="completed"' + (o.status==='completed'?' selected':'') + '>Completed</option>';
      html += '<option value="escalated"' + (o.status==='escalated'?' selected':'') + '>Escalated</option>';
      html += '</select>';
      html += '<button class="btn-sm" data-uid="'+esc(uid)+'" data-action="toggle-note">' + (o.notes ? 'Edit note' : 'Add note') + '</button>';
      html += '<button class="btn-sm" data-uid="'+esc(uid)+'" data-action="toggle-detail">Details</button>';
      html += '</div>';

      // Hidden detail panel (admin info for when you need it)
      html += '<div class="fault-detail" id="detail-'+esc(uid)+'">';
      html += '<span><span class="detail-label">Fault ID:</span> ' + esc(fid) + '</span>';
      html += '<span><span class="detail-label">Area:</span> ' + esc(f[2]) + '</span>';
      html += '<span><span class="detail-label">Created:</span> ' + esc(f[3]) + '</span>';
      html += '<span><span class="detail-label">IMTRAC status:</span> ' + esc(f[4]) + '</span>';
      if (f[8] && f[8] !== '-' && f[8] !== 'None') html += '<span><span class="detail-label">Fault code:</span> ' + esc(f[8]) + '</span>';
      if (f[6]) html += '<span><span class="detail-label">Full additional info:</span> ' + esc(f[6]) + '</span>';
      if (f[7] && f[7] !== 'None') html += '<span><span class="detail-label">Technician close comments:</span> ' + esc(f[7]) + '</span>';
      if (f[9] && f[9] !== 'See comments' && f[9] !== 'None') html += '<span><span class="detail-label">Fault description:</span> ' + esc(f[9]) + '</span>';
      html += '</div>';

      // Note editor
      html += '<div class="note-input" id="note-'+esc(uid)+'">';
      html += '<textarea placeholder="Add a note about this fault...">' + esc(o.notes) + '</textarea>';
      html += '<button class="note-save" data-area="'+esc(f[2])+'" data-fid="'+esc(fid)+'" data-uid="'+esc(uid)+'" data-action="save-note">Save note</button>';
      html += '</div>';
      
      html += '</div>';
    }
    
    html += '</div></div>';
  }
  
  if (total === 0) {
    html = '<div class="empty">No faults match your filters.</div>';
  }
  
  container.innerHTML = html;
}

// Event delegation — all clicks and changes handled here, no inline handlers
document.addEventListener('click', function(e) {
  // Category section toggle
  const catHeader = e.target.closest('[data-toggle="cat"]');
  if (catHeader) {
    catHeader.parentElement.classList.toggle('collapsed');
    return;
  }

  // Toggle note panel
  const noteBtn = e.target.closest('[data-action="toggle-note"]');
  if (noteBtn) {
    const el = document.getElementById('note-' + noteBtn.dataset.uid);
    if (el) el.classList.toggle('visible');
    return;
  }

  // Toggle detail panel
  const detailBtn = e.target.closest('[data-action="toggle-detail"]');
  if (detailBtn) {
    const el = document.getElementById('detail-' + detailBtn.dataset.uid);
    if (el) el.classList.toggle('visible');
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
  // Status dropdown
  const sel = e.target.closest('[data-action="status"]');
  if (sel) {
    const o = getOutcome(sel.dataset.area, sel.dataset.fid);
    setOutcome(sel.dataset.area, sel.dataset.fid, sel.value, o.notes);
    render();
    return;
  }
});

// Init
loadLocalState();
loadFromServer().then(() => render());

document.getElementById('areaFilter').addEventListener('change', render);
document.getElementById('statusFilter').addEventListener('change', render);
document.getElementById('searchInput').addEventListener('input', render);
</script>
</body>
</html>
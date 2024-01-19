<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

global $gw_eras;

// A list of all the forum software eras in Gaming World history.
$gw_eras = [
  [
    'slug' => 'snitz_2000',
    'start' => 986485571,
    'start_ts' => '2001-04-05T15:46:11.000Z',
    'name' => 'Snitz Forums 2000 v3.1 SR4',
    'vendor' => 'Snitz Communications',
  ],
  [
    'slug' => 'openbb_100',
    'start' => 1007250135,
    'start_ts' => '2001-12-01T23:42:15.000Z',
    'name' => 'Open Bulletin Board 1.0.0rc2',
    'vendor' => 'Iansoft Enterprises',
  ],
  [
    'slug' => 'ipb_10',
    'start' => 1028331012,
    'start_ts' => '2002-08-02T23:30:12.000Z',
    'name' => 'Invision Power Board v1.0 beta-v1.3 final',
    'vendor' => 'Invision Power Services',
  ],
  [
    'slug' => 'vbulletin_307',
    'start' => 1109484057,
    'start_ts' => '2005-02-27T06:00:57.000Z',
    'name' => 'vBulletin v3.0.7',
    'vendor' => 'Jelsoft Enterprises Limited',
  ],
  [
    'slug' => 'smf_106',
    'start' => 1140393547,
    'start_ts' => '2006-02-19T23:59:07.000Z',
    'name' => 'Simple Machines Forum v1.0.6',
    'vendor' => 'Lewis Media',
  ],
  [
    'slug' => 'smf_200',
    'start' => 1290916747,
    'start_ts' => '2010-11-28T03:59:07.000Z',
    'name' => 'Simple Machines Forum v2.0.0',
    'vendor' => 'Simple Machines LLC',
  ],
  [
    'slug' => 'ipb_341',
    'start' => 1357860344,
    'start_ts' => '2013-01-10T23:25:44.000Z',
    'name' => 'Invision Power Board v3.4.1',
    'vendor' => 'Invision Power Services',
  ],
  [
    'slug' => 'smf_2019',
    'start' => 1704920444,
    'start_ts' => '2024-01-10T22:00:44.000Z',
    'name' => 'Simple Machines Forum v2.0.19',
    'vendor' => 'Simple Machines LLC',
  ],
];


/**
 * Returns an era indicator for a given Unix time.
 */
function get_gw_era_from_timestamp($unixtime) {
  global $gw_eras;

  if (empty($unixtime)) {
    return null;
  }

  foreach (array_reverse($gw_eras) as $era) {
    if ($unixtime > $era['start']) {
      return $era;
    }
  }
  
  return null;
}

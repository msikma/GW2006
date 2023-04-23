<?php
// Gaming World 2006 <https://gamingw.net/>
// © MIT License

use Goat1000\SVGGraph\SVGGraph;

global $gw_stats_settings;

$gw_stats_settings = [
  'defaults' => [
    'auto_fit' => true,
    'back_colour' => 'none',
    'back_stroke_width' => 0,
    'back_stroke_colour' => '#eee',
    'stroke_colour' => '#000',
    'axis_colour' => '#333',
    'axis_overlap' => 2,
    'grid_colour' => '#666',
    'label_colour' => '#000',
    'semantic_classes' => true,
    'axis_font' => 'Verdana',
    'axis_font_size' => 10,
    'fill_under' => [true, false],
    'sort' => false,
    'pad_right' => 0,
    'pad_left' => 0,
    'marker_type' => ['circle', 'square'],
    'marker_size' => 3,
    'marker_colour' => ['blue', 'red'],
    'minimum_grid_spacing' => 20,
    'show_subdivisions' => true,
    'show_grid_subdivisions' => true,
    'grid_subdivision_colour' => '#ccc',
    'best_fit' => 'straight',
    'best_fit_colour' => ['red', 'blue', 'green', 'orange'],
    'best_fit_dash' => '2,2',
    'line_curve' => [0.75, 0.9],
  ],
  'pie_chart' => [
    'structure' => [
      'key' => 0,
      'value' => 1
    ],
    'structured_data' => true,
    'back_stroke_width' => 0,
    'show_data_labels' => false,
    'stroke_width' => 0,
    'sort' => false,
    'keep_colour_order' => true,
    'start_angle' => 0,
    'end_angle' => 360,
  ],
];

/**
 * Converts user activity data from SMF to a format our bar chart library can handle.
 * 
 *   [
 *     {
 *       "hour": 0,
 *       "hour_format": "12 am",
 *       "posts": "341",
 *       "posts_percent": 8,
 *       "is_last": false,
 *       "relative_percent": 82
 *     },
 *     ... etc.
 */
function activity_rows($user_posts_by_time) {
  $rows = [];
  foreach ($user_posts_by_time as $hour) {
    $rows[$hour['hour_format']] = intval($hour['relative_percent']);
  }
  return $rows;
}

/**
 * Generates a relative activity chart.
 * 
 * Note: this uses the raw user activity data.
 */
function get_user_activity_chart($user_posts_by_time) {
  global $gw_stats_settings;
  $width = 796;
  $height = 500;
  $type = 'LineGraph';
  $values = activity_rows($user_posts_by_time);
  $colours = [['var(--gw-line-chart-top)', 'var(--gw-line-chart-bottom)']];
  $graph = new SVGGraph($width, $height, $gw_stats_settings['defaults']);
  $graph->colours($colours);
  $graph->values($values);
  $chart = $graph->fetch($type, false);
  return '<span class="chart activity_chart">'.$chart.'</span>';
}

/**
 * Generates a pie chart for indicating a subforum's relative popularity.
 */
function get_percentage_pie_chart($percentage) {
  global $gw_stats_settings;
  $percentage = max($percentage, 1);
  $width = 400;
  $height = 400;
  $type = 'PieGraph';
  $values = [
    ['A', 100 - $percentage],
    ['B', $percentage],
  ];
  $colours = ['var(--gw-stats-pie-empty)', 'var(--gw-stats-pie-full)'];
  $graph = new SVGGraph($width, $height, array_merge($gw_stats_settings['defaults'], $gw_stats_settings['pie_chart']));
  $graph->colours($colours);
  $graph->values($values);
  $chart = $graph->fetch($type, false);
  return '<span class="chart pie_chart">'.$chart.'</span></span>';
}

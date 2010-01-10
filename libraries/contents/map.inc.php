<?php
/**
 * Copyright (C) 2009, WARDIYONO (wynerst@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

$title = 'Library Location';
$lat = -6.2254549;
$long = 106.8023901;

?>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
function initialize() {
  var latlng = new google.maps.LatLng(<?php echo $lat . ',' . $long; ?>);
  var myOptions = {
    zoom: 14,
    center: latlng,
    mapTypeId: google.maps.MapTypeId.ROADMAP
    };

  var map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
  var marker = new google.maps.Marker({
      position: latlng,
      map: map,
      title:"<?php echo $info; ?>"
    });
}
// initialize when document ready
jQuery('document').ready(function() {
  initialize();
})
</script>
<div id="map-canvas" style="width: 100%; height: 100%;"></div>

define([
    'jquery',
    'jquery/ui',
    'BrainActs_StoreLocator/js/map'
], function($){
    $.widget('conns.map', $.mage.storelocatorMap, {
        setupMarker: function (item) {

            var image = {
                url: this.options.icon,
                // This marker is 20 pixels wide by 32 pixels high.
                size: new google.maps.Size(48, 48),
                // The origin for this image is (0, 0).
                origin: new google.maps.Point(0, 0),
                // The anchor for this image is the base of the flagpole at (0, 32).
                anchor: new google.maps.Point(0, 32)
            };

            var positionMarker = new google.maps.LatLng(parseFloat(item.latitude), parseFloat(item.longitude));

            var marker = new google.maps.Marker({
                position: positionMarker,
                title: item.name,
                map: this.googleMap,
                icon: image
            });

            var infowindow = new google.maps.InfoWindow({
                content: item.popupContent
            });

            marker.addListener('click', function() {
                infowindow.open(this.googleMap, marker);
            });


            this.bounds.extend(marker.getPosition());

            var index = this.markers.length;
            this.markers[index] = marker;

            return index;
        }
    });
    return $.conns.map;
});
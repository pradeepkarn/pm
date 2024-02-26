<script>
    function initializeMap(mapContainer, accessToken, divId, latId, lonId, placeHolder) {
        mapboxgl.accessToken = accessToken;
        // Initialize the Mapbox map
        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v11', // You can use your desired map style
            center: [0, 0], // Initial center coordinates
            zoom: 1, // Initial zoom level
        });
        // Initialize the Mapbox Geocoder control
        var geocoder = new MapboxGeocoder({
            accessToken: accessToken,
            mapboxgl: mapboxgl,
        });

        // Add the geocoder control to the form
        document.getElementById(divId).appendChild(geocoder.onAdd(map));

        // Customize the geocoder input placeholder
        document.getElementsByClassName('mapboxgl-ctrl-geocoder--input')[0].setAttribute("placeholder", placeHolder);

        // Listen for the result event
        geocoder.on('result', function(e) {
            var coordinates = e.result.geometry.coordinates;
            // var locationName = e.result.text;
            // var place_name = e.result.place_name;
            // document.getElementById('set-location').value = place_name;
            document.getElementById(latId).value = coordinates[1];
            document.getElementById(lonId).value = coordinates[0];
        });

        return map;
    }
    // Example usage
    
</script>
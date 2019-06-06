fetch(ImageProxy_boilerplate_ajax__vars.ajax_url_action)
    .then(function (response) {
        return response.json();
    })
    .then(function (r) {
        console.log(r);
    })
    .catch(function (e) {
        console.log(e);
    });

/**
 * Created by Marcos Regis on 15/02/2018.
 */

require('../css/app.scss');

var $ = require('jquery');

require('fontawesome');
// JS is equivalent to the normal "bootstrap" package
// no need to set this to a variable, just require it
require('bootstrap');
require('startbootstrap-sb-admin/js/sb-admin');
// require('startbootstrap-sb-admin');
require('jquery.easing');

$(document).ready(function () {
    $("#myModal").modal("show");
    });


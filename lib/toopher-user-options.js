(function($, dest){

  var paired = null;
  var iframeLoading = null;
  var iframeLoaded = null;
  var container = null;
  var trigger = null;
  var failed = null;
  var toopherWebApi = null;
  var iframeContainer;

  var init = function(_toopherWebApi, _containerId, _pairingToggleClass, _initialState){
    toopherWebApi = _toopherWebApi;
    paired = _initialState === 'paired';
    iframeLoading = false;
    iframeLoaded = false;
    container = $('#' + _containerId);
    trigger = $('.' + _pairingToggleClass);
    iframeContainer = $('#toopher_iframe_container');

    trigger.click(function(){
      iframeLoading = true;
      iframeLoaded = false;
      refreshUI();
      // get the link
      $.getJSON(ajaxurl, 
        data = {
          'action': 'toopher_get_pair_url_for_current_user'
        }, 
        success = function(response) {
          container.children('.toopher-show-while-iframe-loading').hide();
          var iframe = $('<iframe id="toopher_iframe" style="height: 100%; width: 100% ;" />')
            .attr('toopher_req', response.toopher_req)
            .attr('toopher_postback', ajaxurl)
            .attr('framework_post_args', response.framework_post_args)
            .attr('use_ajax_postback', 'true');
          iframeContainer.append(iframe);
          toopherWebApi.init(iframe, false, update);
        }


      ).fail(function(){
        iframeLoaded = false;
        iframeLoading = false;
        failed = true;
        refreshUI();
      });

    });
    setTimeout(function(){
      if(iframeLoading){
        iframeLoading = false;
        failed = true;
        refreshUI();
      }
    }, 10000);

    refreshUI();
  }

  var refreshUI = function(){

    if (iframeLoading){
      container.find('.toopher-show-while-iframe-loading').show();
    } else {
      container.find('.toopher-show-while-iframe-loading').hide();
    }

    if (iframeLoaded){
      container.find('.toopher-hide-when-iframe-loaded').hide();
      container.find('.toopher-show-when-iframe-loaded').show();
    } else {
      container.find('.toopher-show-when-iframe-loaded').hide();
      container.find('.toopher-hide-when-iframe-loaded').show();
    }

    if (paired){
      container.find('.toopher-show-when-unpaired').hide();
      container.find('.toopher-show-when-paired').show();
    } else {
      container.find('.toopher-show-when-paired').hide();
      container.find('.toopher-show-when-unpaired').show();
    }

    if (failed){
      container.find('.toopher-show-on-failure').show();
    }
  }

  var update = function(data) {
    iframeLoading = false;
    if(data.status === 'ready'){
      failed=false;
      iframeLoaded = true;
    } else if(data.status === 'toopher-api-complete') {
      failed=false;
      iframeContainer.empty();
      iframeLoaded = false;
      paired = data.payload.paired;
    }
    refreshUI();
  }

  var exports = {};
  exports.init = init;

  return exports;

})(jQuery, window)

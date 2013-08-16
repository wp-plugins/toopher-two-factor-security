(function($){
  var _global = this;
  var statusCallback = null;

  var getTerminalId = function(iframe, callback) {
    var terminalName = $.cookie('toopher_terminal_name');
    if (terminalName) {
      callback(terminalName);
    } else {
      iframe.hide();
      // first time authenticating with Toopher from this terminal - prompt the user to name the terminal

      var tDiv = $('<div>Please enter a name for this terminal:<input type="text" id="toopher_terminal_name_input"></input><input id="toopher_terminal_name_button" type="button" value="OK"></input></div>');
      tDiv.children('#toopher_terminal_name_button').click(function(){
        terminalName = tDiv.children('#toopher_terminal_name_input').val();
        cookieOptions = { expires : 365, path: '/' };
        if (location.protocol === 'https:') {
          cookieOptions.secure = true;
        }
        $.cookie('toopher_terminal_name', terminalName, cookieOptions);
        tDiv.remove();
        iframe.show();
        callback(terminalName);
      });
      iframe.before(tDiv);
    }
    
  }

  var postToUrl = function (path, params, method){
    method = method || 'POST';
    var form = $('<form />').attr('method', method).attr('action', path);
    for (var key in params){
      if (params.hasOwnProperty(key)){
        console.log('adding form field "' + key + '" = "' + params[key] + '"');
        var hiddenField = $('<input />').attr('type', 'hidden').attr('name', key).attr('value', params[key]);
        form.append(hiddenField);
      }
    }
    $('body').append(form);
    form.submit();
  }

  var handleMessage = function(e){
    console.log('handled message');
    console.log(e.data);
    if (e.data.status === 'toopher-api-complete'){
      var iframe = $('#toopher_iframe');
      var frameworkPostArgsJSON = iframe.attr('framework_post_args');
      var frameworkPostArgs = {};
      if(frameworkPostArgsJSON){
        frameworkPostArgs = $.parseJSON(frameworkPostArgsJSON);
      }
      var postData = $.extend({}, e.data.payload, frameworkPostArgs);
      if(iframe.attr('use_ajax_postback')){
      $.post(iframe.attr('toopher_postback'), postData)
        .done(function(data){
          data = $.parseJSON(data);
          if(statusCallback){
            statusCallback({'status': e.data.status, 'payload': data});
          }
        });
      } else {
        postToUrl(iframe.attr('toopher_postback'), postData, 'POST');
      }
    } else {
      if (statusCallback){
        statusCallback(e.data);
      }
    }
  }
  window.addEventListener('message', handleMessage, false);

  $(document).ready(function(){
    var iframe = $('#toopher_iframe');
    if (!iframe.length){
      // no toopher iframe present
    } else {
      init(iframe);
    }
  });

  var init = function(target, _statusCallback){
    statusCallback = _statusCallback;
    getTerminalId(target, function(terminalName){
      toopher_url = target.attr('toopher_req') + '&terminal_name=' + encodeURIComponent(terminalName);
      target.attr('src', toopher_url);
    });
  }

  var exports = {};
  exports.init = init;

  return exports;

})(jQuery)

jQuery(document).ready(function($) {
  var renderRepos = function(repos) {
    var fragment = '';
    for (var i = 0; i < repos.length; i++) {
      fragment += '<li><a href="'+repos[i].html_url+'" target="_blank">'+repos[i].name+'</a><p>'+repos[i].description+'</p></li>';
    }
    $('#gh-repos').html(fragment);
  }

  var renderActivity = function(activity) {
    var actions = {
      'PushEvent': function(a) {
        return 'Pushed to ' + a.repo.name;
      },
      'WatchEvent': function(a) {
        return 'Starred ' + a.repo.name;
      },
      'IssueCommentEvent': function(a) {
        return 'Commented on ' + a.repo.name;
      },
      'IssuesEvent': function(a) {
        return a.payload.action + ' issue ' + '<a href="'+a.payload.issue.html_url+'">'+a.repo.name;
      }
    };
    console.log(activity);
    var fragment = '';
    for (var i = 0; i < activity.length; i++) {
      fragment += '<li>'+actions[activity[i].type](activity[i])+'</li>';
    }
    $('#gh-activity').html(fragment);
  }

  var options = BetterGitHubWidget;
  if (options.display_repos == 'true') {
    var url = 'https://api.github.com/users/'+options.username+'/repos?sort=updated';
    $.getJSON(url, function(data) {
      if (!data) return;
      var repos = [];
      for (var i = 0; i < data.length; i++) {
        if (options.skip_forks && data[i].fork)
          continue;
        repos.push(data[i]);
      }
      if (options.count && options.count > 0)
        repos.splice(options.count);
      renderRepos(repos);
    });
  }
  if (options.display_activity == 'true') {
    var data = 'user='+options.username+'&action=bgw_get_activity';
    $.post(options.ajaxurl, data, function(resp) {
      var activity = resp;
      activity.splice(5);
      renderActivity(activity);
    }, 'json');
  }
});

var github = (function(){
  function render(repos){
    var i = 0, fragment = '';
    var t = document.getElementById('gh-repos');
    for(i = 0; i < repos.length; i++) {
      fragment += '<li><a href="'+repos[i].html_url+'">'+repos[i].name+'</a><p>'+repos[i].description+'</p></li>';
    }
    t.innerHTML = fragment;
  }
  return {
    showRepos: function(options){
      var req = new XMLHttpRequest();
      req.open("GET", "https://api.github.com/users/"+options.user+"/repos", true);
      req.onreadystatechange = function (oEvent) {
        if (req.readyState == 4) {
          if (req.status == 200) {
            var data = JSON.parse(req.responseText);
            var repos = [];
            if (!data) return;
            for (var i = 0; i < data.length; i++) {
              if (options.skip_forks && data[i].fork) continue;
              repos.push(data[i]);
            }
            repos.sort(function(a, b) {
              var aDate = new Date(a.pushed_at).valueOf(),
                  bDate = new Date(b.pushed_at).valueOf();

              if (aDate === bDate) { return 0; }
              return aDate > bDate ? -1 : 1;
            });
            if (options.count) {
              repos.splice(options.count);
            }
            render(repos);
          } else {
            document.getElementById('gh-loading').innerHTML = "Error loading feed";
            console.log("Error", req.statusText);
          }
        }
      }
      req.send(null);
    }
  };
})();

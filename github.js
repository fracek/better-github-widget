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
    options: {},
    parseResult: function(result) {
      if (!result || !result.data) {
        return;
      }
      var data = result.data;
      var repos = [];
      for (var i = 0; i < data.length; i++) {
        if (this.options.skip_forks && data[i].fork) {
          continue;
        }
        repos.push(data[i]);
      }
      repos.sort(function(a, b) {
        var aDate = new Date(a.pushed_at).valueOf(),
            bDate = new Date(b.pushed_at).valueOf();

        if (aDate === bDate) { return 0; }
        return aDate > bDate ? -1 : 1;
      });
      if (this.options.count) {
        repos.splice(this.options.count);
      }
      render(repos);
    },
    showRepos: function(options){
      var req = "https://api.github.com/users/"+options.user+"/repos?callback=github.parseResult";
      var head = document.getElementsByTagName("head").item(0);
      var script = document.createElement("script");
      this.options = options;
      script.setAttribute("type", "text/javascript");
      script.setAttribute("src", req);
      head.appendChild(script);   
    }
  };
})();

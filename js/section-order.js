jQuery(document).ready(function($) {
  $('.bgw-sections-order').sortable({
    items: 'tbody tr',
    cursor: 'move',
    axis: 'y',
    update: function() {
      var order = $(this).sortable('serialize') +
          '&action=bgw_update_order&nonce=' + SectionOrder.nonce;
      $.post(ajaxurl, order, function(response) {
        console.log(response);
      });
    }
  });
});

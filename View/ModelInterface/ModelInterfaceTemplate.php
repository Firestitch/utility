<?php
use Framework\Util\HtmlUtil;
use Utility\View\Namespaces\NamespacesView;

?>
<h1>Model Interface</h1>

<div class="row row-container">
  <div class="col">
    <?php NamespacesView::create()->setName("namespace")->show(); ?>

    <div class="form-field">
      <div class="lbl">Model</div>
      <div id="sourceModels"></div>
    </div>

    <div class="form-field">
      <div class="lbl">Interface Directory</div>
      <?php echo HtmlUtil::dropdown("interfaceDir", $interfaceDirs, array_keys($interfaceDirs)[0]) ?>
    </div>

    <?php echo HtmlUtil::button("update", "Update", ["class" => "btn-primary"]) ?>
  </div>

  <div class="col">
    <div class="lbl">Output</div>
    <code id="output"></code>
  </div>
  <div class="cb"></div>
</div>


<script>
  function camelize(s) {
    return s.replace(/([-_][a-z])/ig, ($1) => {
      return $1.toUpperCase()
        .replace('-', '')
        .replace('_', '');
    });
  }

  $(function () {
    $(".namespace")
      .on("change", function () {
        $("#sourceModels").load("/model/list", {
          namespace: $('.namespace').val(),
          name: 'sourceModel',
          limit: 30
        }, function () {
          $("select[name='sourceModel']").bind("click keyup", function () {
            $.post("/model/interface/api/preview", $("#form").serializeArray(), function (response) {
              $('#output').html(response);
            });
          });
        });
      }).trigger('change');

    $("#update")
      .click(function () {
        $.post("/model/interface/api/update", $("#form").serializeArray(), function (response) {
          displayResponse(response, 'Successfully updated');
        });
      });

  });
</script>
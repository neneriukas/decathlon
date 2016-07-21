<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;

Yii::app()->clientScript->registerScript('disableSubmit', "
	$(document).ready(
    function(){
        $('input:submit').attr('disabled',true);

        $('input:file').change(
            function(){
                if ($(this).val()){
                    $('input:submit').removeAttr('disabled'); 
                }
                else {
                    $('input:submit').attr('disabled',true);
                }
            });
    });
");

?>

<?php
       $form=$this->beginWidget('CActiveForm', array(
        'id'=>'upload-form',
         'enableAjaxValidation'=>true,
             'htmlOptions' => array('enctype' => 'multipart/form-data'),
        ));
?>
    <div class='well text-center'>
        <p>Please upload CSV file</p>
        <?php echo $form->fileField($model, 'csv'); ?>
        <?php echo $form->error($model, 'csv'); ?>
        <br/>
        <?php  echo CHtml::submitButton('Generate XML', array("class"=>"btn btn-success")); ?>
        <?php echo $form->errorSummary($model); ?>
    </div>
<?php $this->endWidget(); ?>
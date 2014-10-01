<?php namespace Mascame\Artificer\Fields\Types\Relations;

use HTML;
use Request;
use Route;
use Session;

// Todo: attach somehow the new created items to the a new item (which have not yet been created)

class hasMany extends Relation {

	public function boot()
	{
		//$this->addWidget(new Chosen());
		$this->addAttributes(array('class' => 'chosen'));
		$this->modelObject = \App::make('artificer-model');
	}

	public function input()
	{
		$this->fields = array_get(\View::getShared(), 'fields');
		$id = $this->fields['id']->value;
		$options = $this->fieldOptions;
		$this->relation = $options['relationship'];
		$modelName = $this->relation['model'];
		$model = $this->modelObject->models[$modelName];
		$model['class'] = $this->modelObject->getClass($modelName);
		$this->model = $model;

        if ((Route::currentRouteName() == 'admin.create' || Route::currentRouteName() == 'admin.field')
            && Session::has('_set_relation_on_create_'.$this->modelObject->name)) {
            $relateds = Session::get('_set_relation_on_create_'.$this->modelObject->name);

            $related_ids = array();
            foreach ($relateds as $related) {
                $related_ids[] = $related['id'];
            }

            $data = $relateds[0]['modelClass']::whereIn('id', $related_ids)->get()->toArray();
        } else {
            $data = $model['class']::where($this->relation['foreign'], '=', $id)->get(array('id', $this->relation['show']))->toArray();
        }

        $this->showItems($data);

		$this->createURL = $this->createURL($model['route']) . "?" . http_build_query(array($this->relation['foreign'] => $id, '_standalone' => 'true'));

		if (!Request::ajax()) {
			$this->relationModal();
		}
	}

	public function showItems($data)
	{
		if (!Request::ajax()) { ?>
			<div data-refresh-field="<?= \URL::route('admin.field',
				array('slug'  => $this->modelObject->getRouteName(),
					  'id'    => ($this->fields['id']->value) ? $this->fields['id']->value : 0,
					  'field' => $this->name)) ?>">
		<?php }

			if (!empty($data)) { ?>
				<ul class="list-group">
					<?php foreach ($data as $item) {
						$this->addItem($item);
					} ?>
				</ul>
			<?php } else { ?>
				<div class="well well-sm">No items yet</div><?php
			}

		if (!Request::ajax()) { ?>
			</div>
		<?php }
	}

	public function addItem($item) {
		$edit_url = $this->editURL($this->model['route'], $item['id']).'?'. http_build_query(array('_standalone' => 'true'));
		?>
		<li class="list-group-item">
			<?= $item[$this->relation['show']] ?> &nbsp;

			<span class="right">
				<span class="btn-group">
					<button class="btn btn-default" data-toggle="modal"
							data-url="<?=$edit_url?>"
							data-target="#form-modal-<?= $this->model['route'] ?>">
						<i class="glyphicon glyphicon-edit"></i>
					</button>
					<a data-method="delete" data-token="<?= csrf_token() ?>"
					   href="<?= route('admin.destroy', array('slug' => $this->model['route'], 'id' => $item['id']), $absolute = true) ?>"
					   type="button" class="btn btn-default">
						<i class="glyphicon glyphicon-remove"></i>
					</a>
				</span>
			</span>

		</li>
		<?php
	}

	public function show($values = null)
	{
		if (isset($values) && !$values->isEmpty()) {
			$show = $this->fieldOptions['relationship']['show'];

			foreach ($values as $value) {
				print $value->$show . "<br>";
			}
		} else {
			print "<em>(none)</em>";
		}
	}

}
<?php

use Layla\API;

class Admin_Account_Page {

	public function read_multiple($view, $accounts)
	{
		$templates = array(
			'listitem' => View::make('admin::pages.accounts.listitem')
		);

		$view->notifications();

		$view->full_list(function($view) use ($accounts, $templates)
		{
			$view->header(function($view)
			{
				$view->search();
				$view->tabs(function($tab)
				{
					$tab->add('<i class="icon-list"></i>');
					$tab->add('<i class="icon-tree"></i>');
				});
			});

			$view->items(function($view) use ($accounts, $templates)
			{
				if(count($accounts->results) > 0)
				{
					foreach ($accounts->results as $account)
					{
						$view->add($templates['listitem']->with('account', $account)->render());
					}
				}
				else
				{
					if(Input::get('q'))
					{
						$view->no_results(__('admin::account.read_multiple.table.no_search_results'));
					}
					else
					{
						$view->no_results(__('admin::account.read_multiple.table.no_results'));
					}
				}
			});
		});

		$view->templates($templates);
	}

	public function create($view)
	{
		$view->form(function($view)
		{
			$view->page_header(function($view)
			{
				$view->title('ADD ACCOUNT');
			});
			
			// Get Roles and put it in a nice array for the dropdown
			$roles = array('' => '') + model_array_pluck(API::get(array('roles'))->get('results'), function($role)
			{
				return $role->lang->name;
			}, 'id');

			// Get Languages and put it in a nice array for the dropdown
			$languages = model_array_pluck(API::get(array('languages'))->get('results'), function($language)
			{
				return $language->name;
			}, 'id');

			$view->text('name',  __('admin::account.create.form.name'), Input::old('name'));
			$view->text('email', __('admin::account.create.form.email'), Input::old('email'));
			$view->password('password', __('admin::account.create.form.password'));
			$view->multiple('roles[]', __('admin::account.create.form.roles'), $roles, Input::old('roles'));
			$view->dropdown('language_id', __('admin::account.create.form.language'), $languages, Input::old('language_id'));

			$view->actions(function($view)
			{
				$view->submit(__('admin::account.create.buttons.add'), 'primary');
			});
		}, 'POST', prefix('admin').'account/add');
	}

	public function update($view, $account)
	{
		$view->form(function($view) use ($account)
		{
			$view->page_header(function($view)
			{
				$view->title('EDIT ACCOUNT');
			});

			// The response body is the Account
			$account = $account->get();
			
			// Get Roles and put it in a nice array for the dropdown
			$roles = array('' => '') + model_array_pluck(API::get(array('roles'))->get('results'), function($role)
			{
				return $role->lang->name;
			}, 'id');

			// Get the Roles that belong to a User and put it in a nice array for the dropdown
			$active_roles = array();
			if(isset($account->roles))
			{ 
				$active_roles = model_array_pluck($account->roles, 'id', '');
			}

			// Get Languages and put it in a nice array for the dropdown
			$languages = model_array_pluck(API::get(array('languages'))->get('results'), function($language)
			{
				return $language->name;
			}, 'id');

			$view->text('name',  __('admin::account.update.form.name'), Input::old('name', $account->name));
			$view->text('email', __('admin::account.update.form.email'), Input::old('email', $account->email));
			$view->password('password', __('admin::account.update.form.password'));
			$view->multiple('roles[]', __('admin::account.update.form.roles'), $roles, Input::old('roles', $active_roles));
			$view->dropdown('language_id', __('admin::account.update.form.language'), $languages, Input::old('language_id', $account->language->id));

			$view->actions(function($view)
			{
				$view->submit(__('admin::account.update.buttons.edit'), 'primary');
			});
		}, 'PUT', prefix('admin').'account/'.$account->get('id').'/edit');
	}

	public function delete($view, $id)
	{
		// Get the Account
		$response = API::get(array('account', $id));

		// Handle response codes other than 200 OK
		if( ! $response->success)
		{
			return Event::first($response->code);
		}

		// The response body is the Account
		$account = $response->get();

		$view->page_header(function($view)
		{
			$view->float_right(function($view)
			{
				$view->search();
			});

			$view->title(__('admin::account.delete.title'));
		});

		$view->well(function($view) use ($account)
		{
			$view->raw(__('admin::account.delete.message', array('name' => $account->name, 'email' => $account->email)));
		});

		$view->form(Module::form('account.delete', $id), 'DELETE', prefix('admin').'account/delete/'.$id);		
	}

}
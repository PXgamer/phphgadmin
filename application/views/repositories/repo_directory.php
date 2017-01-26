<table class="table">
    <tr>
        <td colspan="4">
            <p>
                <a href="#" id="dialog_link2_create" class="btn btn-default" data-toggle="modal"
                   data-target="#create_modal">
                    <span class="fa fa-fw fa-plus"></span> Create
                </a>
                <a href="#" id="dialog_link2_delete" class="btn btn-danger" data-toggle="modal"
                   data-target="#delete_modal">
                    <span class="fa fa-fw fa-trash"></span> Delete
                </a>
            </p>
        </td>
    </tr>
    <tr>
        <th></th>
        <th width="100%">Title</th>
        <th>Status</th>
        <th></th>
    </tr>
    <?php $parity = 0;
    while (have_repos()): the_repo(); ?>
        <tr class="parity<?php echo $parity;
        $parity = ($parity + 1) % 2; ?>">
            <td>
                <input title="check" type="checkbox" disabled="disabled"/>
            </td>
            <td>
                <a href="<?= site_url('hgrepo/manage/' . repo_name()); ?>"><?= htmlentities(repo_name()); ?></a>
            </td>
            <td>
                <?php if (repo_status() == HGPHP_REPO_STATUS_ENABLED): echo 'Enabled' ?>
                <?php elseif (repo_status() == HGPHP_REPO_STATUS_DISABLED): echo 'Disabled'; ?>
                <?php elseif (repo_status() == HGPHP_REPO_STATUS_MISSING): echo 'Missing'; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if (repo_status() == HGPHP_REPO_STATUS_ENABLED): ?>
                    <a href="<?= site_url('hgrepo/browse/' . repo_name()); ?>">
                        <span class="fa fa-fw fa-link"></span> Browse
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<div class="modal fade" tabindex="-1" role="dialog" id="create_modal">
    <div class="modal-dialog" role="document">
        <form action="<?php echo site_url('hgdir'); ?>" method="post" id="form_create" name="form_create">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Create Repository</h4>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="form_action" value="create_repository"/>
                    <label for="form_create_name">Repository Name</label>
                    <input type="text" style="width:100%" name="form_create_name" id="form_create_name"
                           autocomplete="off"/>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create Repository</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="delete_modal">
    <div class="modal-dialog" role="document">
        <form action="<?php echo site_url('hgdir'); ?>" method="post" id="form_delete" name="form_delete">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Delete Repository</h4>
                </div>
                <div class="modal-body">
                    <table style="width: 100%">
                        <tr>
                            <td>
                                <input type="hidden" name="form_action" value="delete_repository"/>
                                <label for="form_delete_name">Repository to Delete</label>
                                <select style="width:100%" name="form_delete_name" id="form_delete_name">
                                    <option selected="selected" value=""></option>
                                    <?php while (have_repos()): the_repo(); ?>
                                        <option value="<?php echo repo_name(); ?>"><?php echo repo_name(); ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <br/>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Confirm Deletion</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
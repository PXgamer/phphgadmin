<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#nb-top">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?php echo base_url(); ?>">PhpHgAdmin</a>
        </div>

        <div class="collapse navbar-collapse" id="nb-top">
            <ul class="nav navbar-nav">
                <li><a href="<?php echo base_url(); ?>">Home</a></li>
                <li><a href="/checkinstall/">Check Install</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="active"><a><?=HGPHP_VERSION?></a></li>
            </ul>
        </div>
    </div>
</nav>
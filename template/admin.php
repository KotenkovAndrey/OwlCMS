
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#sidebar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><span>owlstudio.ru</span> - Панель администрирования</a>
            </div>
                            
        </div><!-- /.container-fluid -->
    </nav>
    {$menu}
    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">           
    <div class="wrap">
<div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">{$pagetitle}</div>
                    <div class="panel-body">
                        {$component}    
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>  <!--/.main-->
    }

<footer></footer>
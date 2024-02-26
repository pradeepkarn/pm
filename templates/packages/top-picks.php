    <style>
        /* Set the height and width for the cards */
        .trend-item {
            width: 100%;
            /* You can adjust the width as needed */
            max-width: 300px;
            /* Set a maximum width if required */
            height: auto;
            /* Adjust the height as needed */


        }



        /* Optionally, you can add margin to the card boxes for spacing */
        .trend-box .trend-item {
            margin-bottom: 20px;
        }

        /* Add additional styling as per your design requirements */
    </style>


    <section class="trending pt-6 pb-0 bg-lgrey">
        <div class="container">

            <div class="section-title text-center text-lg-start">
                <h4 class="mb-1 theme1">Top Pick</h4>
                <h2 class=""><?php echo $context->data->cat_name ?? "All Packages"; ?></h2>
                <p><?php echo $context->data->cat_details ?? "Create unforgettable memories with your family in Dubai this summer."; ?></p>
            </div>
            <div class="row">
                <?php
                $packages = $context->data->package_list;
                foreach ($packages as $key => $pkg) :
                    $pkg = obj($pkg);
                ?>

                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="trend-item rounded box-shadow h-100">
                            <div class="trend-image position-relative">
                                <img style="height: 300px; widht:100%; object-fit:cover;" src="/<?php echo MEDIA_URL; ?>/images/pages/<?php echo $pkg->banner; ?>" alt="image" class>
                                <div class="color-overlay"></div>
                            </div>
                            <div class="trend-content p-4 pt-5 position-relative">
                                <div class="trend-meta bg-theme white px-3 py-2 rounded">
                                    <div class="entry-author">
                                        <i class="icon-calendar"></i>
                                        <span class="fw-bold"> <?php echo $pkg->days; ?> Days Tours</span>
                                    </div>
                                </div>
                                <h5 class="theme mb-1"><i class="flaticon-location-pin"></i> <?php echo $pkg->city; ?></h5>
                                <h3 class="mb-1"><a href="/<?php echo home . route('pageBySlug', ['slug' => $pkg->slug]); ?>"><?php echo pk_excerpt($pkg->title, 20); ?></a></h3>
                                <div class="rating-main d-flex align-items-center pb-2">
                                    <!-- <div class="rating">
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star checked"></span>
                                        <span class="fa fa-star checked"></span>
                                    </div>
                                    <span class="ms-2">(12)</span> -->
                                </div>

                                <p class=" border-b pb-2 mb-2">
                                    <?php echo pk_excerpt($pkg->content, 25); ?>
                                </p>
                                <div class="entry-meta">
                                    <div class="entry-author d-flex align-items-center">
                                        <p class="mb-0"><span class="theme fw-bold fs-5"> AED<?php echo $pkg->price; ?></span> | Per person</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
                ?>


                <div class="col-lg-12">
                    <div class="text-center">
                        <a href="<?php echo BASEURI . route('tours'); ?>" class="nir-btn">Load More <i class="fa fa-long-arrow-alt-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
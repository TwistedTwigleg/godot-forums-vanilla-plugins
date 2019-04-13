<style>
    div.Box.Thirdparty-Embed-Discord > iframe.discord
    {
      float:left;
      height:70px;
      width:100%;
      border: 0;
      border-color: transparent;
    }

    @media (hover: hover)
    {
        div.Box.Thirdparty-Embed-Discord > iframe.discord:hover
        {
            height:320px;
            width:100%;
            float:left;
            border: 0;
            border-color: transparent;
        }
    }
    @media (hover: none)
    {
        div.Box.Thirdparty-Embed-Discord > iframe.discord
        {
            height:320px;
        }
    }
</style>

<div class="Box Thirdparty-Embed-Discord">
    <div style="padding-top: 18px"></div>
    
    <iframe class="discord" src= <?php echo "https://discordapp.com/widget?id=" . C('Plugins.DiscordEmbedWidget.DiscordServerID', '') . "&theme=dark" ?>></iframe>
    
    <center><a href="https://discord.gg/PWdeHDW"><i>Alternative Discord from old forums</i></a></center>
    <div style="padding-bottom: 18px"></div>
</div>
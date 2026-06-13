@props(['count' => 1])

@for ($i = 0; $i < $count; $i++)
    <div class="review-card skeleton" style="height: 380px;">
        <div style="padding: 24px; display: flex; flex-direction: column; gap: 16px; height: 100%;">
            <div class="card-topline" style="margin-bottom: 0;">
                <div class="site-icon" style="background: rgba(148, 163, 184, 0.12); border: none;"></div>
                <div class="rating-badge" style="background: rgba(148, 163, 184, 0.12); border: none; width: 60px;"></div>
            </div>
            
            <div style="height: 24px; background: rgba(148, 163, 184, 0.12); border-radius: 4px; width: 80%;"></div>
            
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="height: 14px; background: rgba(148, 163, 184, 0.12); border-radius: 4px; width: 100%;"></div>
                <div style="height: 14px; background: rgba(148, 163, 184, 0.12); border-radius: 4px; width: 90%;"></div>
                <div style="height: 14px; background: rgba(148, 163, 184, 0.12); border-radius: 4px; width: 95%;"></div>
            </div>

            <div class="card-tags" style="margin-top: auto; padding-top: 16px; display: flex; gap: 8px;">
                <div style="height: 26px; width: 70px; background: rgba(148, 163, 184, 0.12); border-radius: 999px;"></div>
                <div style="height: 26px; width: 85px; background: rgba(148, 163, 184, 0.12); border-radius: 999px;"></div>
                <div style="height: 26px; width: 60px; background: rgba(148, 163, 184, 0.12); border-radius: 999px;"></div>
            </div>
        </div>
    </div>
@endfor

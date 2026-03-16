<div style="background: white; border-radius: 15px; border: 1px solid #e9ecef; margin-bottom: 20px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <div style="background: #1e293b; padding: 12px; color: #fbbf24; font-weight: 900; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
        Détails de la prise en charge
    </div>

    <div id="container-volume" style="display: none; padding: 15px;">
        <div style="color: #3b82f6; font-weight: 900; font-size: 10px; text-transform: uppercase; margin-bottom: 10px;">📦 Articles au Volume (m³)</div>
        <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
            <tbody id="recap-volume-body"></tbody>
        </table>
    </div>

    <div id="container-poids" style="display: none; padding: 15px;">
        <div style="color: #f97316; font-weight: 900; font-size: 10px; text-transform: uppercase; margin-bottom: 10px;">⚖️ Articles au Poids (kg)</div>
        <table style="width: 100%; font-size: 12px; border-collapse: collapse;">
            <tbody id="recap-poids-body"></tbody>
        </table>
    </div>
</div>

<div style="background: #0f172a; color: white; border-radius: 20px; padding: 25px; margin-bottom: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3); border: 2px solid #3b82f6;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; font-size: 14px; opacity: 0.8;">
        <span>Sous-total (<span id="recap-quantite-totale">0</span> articles)</span>
        <span id="recap-sous-total" style="font-weight: bold;">0.00€</span>
    </div>

    <div id="zone-promotion" style="border-top: 1px solid #1e293b; padding: 15px 0; margin-bottom: 15px;">
        <div id="promo-suggestion" style="display:none;">
            <button type="button" onclick="window.formulaireEvenement.appliquerPromotion()" 
                style="width: 100%; background: #064e3b; border: 1px solid #10b981; color: #10b981; padding: 8px; border-radius: 10px; font-weight: bold; font-size: 10px; cursor: pointer; text-transform: uppercase;">
                Appliquer Réduction Client ?
            </button>
        </div>

        <div id="promo-applied" style="display:none; border-top: 1px solid #1e293b; padding: 15px 0; margin-bottom: 15px; flex-direction: column; gap: 4px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="color: #10b981; font-size: 11px; font-weight: 900; text-transform: uppercase;">
                    ✅ <span id="promo-libelle">Remise</span> appliquée
                </span>
                <span id="promo-valeur" style="color: #10b981; font-weight: 900; font-size: 16px;">-0.00€</span>
            </div>
            <div style="font-size: 9px; color: #64748b; font-style: italic;">
                * Cette remise est appliquée automatiquement selon les conditions commerciales.
            </div>
        </div>

        <input type="hidden" name="reduction_promotionnelle_id" id="applied_promo_id">

        <!-- <div id="promo-applied" style="display:none; color: #10b981; display: flex; justify-content: space-between; font-weight: bold;">
            <span>Réduction appliquée</span>
            <span id="promo-valeur">-0.00€</span>
        </div> -->
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: 900; text-transform: uppercase; font-size: 16px; color: #3b82f6;">Total Final</span>
        <span id="recap-final-total" style="font-size: 32px; font-weight: 900; color: #ffffff;">0.00€</span>
    </div>
</div>

<div style="background: white; border-radius: 15px; padding: 20px; border: 1px solid #e9ecef; margin-bottom: 20px;">
    <h3 style="font-size: 11px; font-weight: 900; text-transform: uppercase; margin-bottom: 15px; color: #4b5563;">Mode de Règlement</h3>
    
    <div style="margin-bottom: 15px;">
        <select id="moyen-paiement" name="moyen_paiement" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #d1d5db; background: #f9fafb; font-weight: bold;">
            <option value="especes">Espèce</option>
            <option value="cheque">Chèque</option>
            <option value="carte">Carte Bleue</option>
            <option value="virement">Virement</option>
            <option value="mobile_money">Mobile</option>
        </select>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div>
            <label style="font-size: 10px; color: #6b7280; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 5px;">Montant Reçu</label>
            <input type="number" id="montant-verse" name="montant_verse" oninput="window.formulaireEvenement.calculerResteAPayer()" 
                style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #d1d5db; font-weight: bold;">
        </div>
        <div>
            <label style="font-size: 10px; color: #6b7280; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 5px;">Reste à payer</label>
            <div id="reste-a-payer" style="padding: 12px; border-radius: 10px; background: #f3f4f6; font-weight: 900; color: #1f2937;">0.00€</div>
        </div>
    </div>

    <div id="dette-confirmation" style="display:none; margin-top: 15px; background: #fff1f2; border: 1px solid #fecdd3; padding: 12px; border-radius: 10px;">
        <label style="display: flex; gap: 10px; align-items: flex-start; cursor: pointer;">
            <input type="checkbox" name="confirmation_dette" style="margin-top: 3px;">
            <span style="font-size: 10px; color: #991b1b; font-weight: bold;">
                Le client reconnaît le solde restant dû.
            </span>
        </label>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 20px;">
    <div style="background: white; border-radius: 15px; padding: 15px; border: 1px solid #e9ecef;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; color: #4b5563;">🖋️ Signature du Client</span>
            <button type="button" onclick="window.formulaireEvenement.clearSignature('client')" style="border:none; background:none; color:#3b82f6; font-size:10px; font-weight:bold; cursor:pointer;">EFFACER</button>
        </div>
        <canvas id="signature-client" style="width: 100%; height: 150px; background: #f9fafb; border-radius: 10px; border: 2px dashed #d1d5db; touch-action: none;"></canvas>
        <input type="hidden" name="signature_client" id="input-sig-client">
    </div>

    <div style="background: white; border-radius: 15px; padding: 15px; border: 1px solid #e9ecef;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; color: #4b5563;">📋 Signature du Collecteur</span>
            <button type="button" onclick="window.formulaireEvenement.clearSignature('collecteur')" style="border:none; background:none; color:#3b82f6; font-size:10px; font-weight:bold; cursor:pointer;">EFFACER</button>
        </div>
        <canvas id="signature-collecteur" style="width: 100%; height: 150px; background: #f9fafb; border-radius: 10px; border: 2px dashed #d1d5db; touch-action: none;"></canvas>
        <input type="hidden" name="signature_collecteur" id="input-sig-collecteur">
    </div>
</div>
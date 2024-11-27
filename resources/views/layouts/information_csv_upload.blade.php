<ul id="billing">
    <li class="billing">
        File name can be changed. Column names and order <strong>should not</strong> be changed.
    </li>
    <li class="billing">
        Rows must be unique based on the combination of ZIP, Type and CustomerType.
        <ul>
            <li class="provider">A file <strong>may</strong> contain multiple rows with the same ZIP if each row has a unique Type and/or CustomerType. For example, the following is acceptable.
                <ul>
                    <li class="data">“00544, 10, Fiber, .99, Residential”</li>
                    <li class="data">“00544, 10, Fiber, .99, Business”</li>
                </ul>
            </li>
        </ul>
        <ul>
            <li class="provider">A file <strong>may not</strong> contain multiple rows with the same ZIP if the Type and/or CustomerType is not unique. For example the following <strong>is not</strong> acceptable.
                <ul>
                    <li class="data">“00544, 10, Fiber, .99, Residential”</li>
                    <li class="data">“00544, 5, Fiber, .74, Residential”</li>
                </ul>
            </li>
        </ul>
    </li>
    <li class="billing">
        <strong>ZIP:&nbsp;</strong> Include 5 digit zip code.
        <ul>
            <li class="provider">Zips with preceding zeros can be added with or without the zeros. For example, 00544 zip code can be entered as 544.</li>
        </ul>
    </li>
    <li class="billing">
        <strong>Speed:&nbsp;</strong> Represents the download speed maximum available in that zip code. This number should be in Mbps when uploaded.
    </li>
    <li class="billing">
        <strong>Type:&nbsp;</strong> The technology type associated with service in the zip code. Please use the following options: 5G Home, Cable, DSL, Fiber, Fixed Wireless, LTE Home, Mobile, Other Copper Wireline, Satellite
    </li>
    <li class="billing"> <strong>Coverage:&nbsp;</strong> Percentage of the zip code area covered by the service. Use the decimal representation. For example, 100% would be 1 and 74% would be 0.74.</li>
    <li class="billing"> <strong>CustomerType:&nbsp;</strong> Business or Residential </li>

</ul>